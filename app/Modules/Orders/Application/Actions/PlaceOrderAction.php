<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\Actions;

use App\Modules\Catalog\Application\Contracts\CheckoutGatewayInterface;
use App\Modules\Catalog\Domain\Exceptions\InsufficientStockException;
use App\Modules\Orders\Application\Data\CheckoutItemInput;
use App\Modules\Orders\Application\Data\DeliveryDetails;
use App\Modules\Orders\Application\OrderStatusLog;
use App\Modules\Orders\Domain\Exceptions\CouponRejectedException;
use App\Modules\Orders\Domain\Exceptions\EmptyCartException;
use App\Modules\Orders\Domain\Exceptions\OfferUnavailableException;
use App\Modules\Orders\Domain\OrderStatus;
use App\Modules\Orders\Infrastructure\Models\Order;
use App\Modules\Promotions\Application\Contracts\CouponGatewayInterface;
use App\Modules\Shared\Events\OrderPlaced;
use App\Modules\Shared\Events\StockSold;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PlaceOrderAction
{
    public function __construct(
        private readonly CheckoutGatewayInterface $catalog,
        private readonly CouponGatewayInterface $coupons,
        private readonly OrderStatusLog $statusLog,
    ) {}

    /**
     * @param  list<CheckoutItemInput>  $items
     */
    public function execute(array $items, DeliveryDetails $delivery, ?int $customerId = null, ?string $couponCode = null, string $channel = 'online', ?int $soldBy = null, string $paymentMethod = 'cod'): Order
    {
        if ($items === []) {
            throw EmptyCartException::create();
        }

        // Resolve every offer up front so we fail on an unknown offer before
        // touching the database, and so all line snapshots share one currency.
        $lines = [];
        $currency = null;

        foreach ($items as $item) {
            $offer = $this->catalog->find($item->offerId);

            if (! $offer) {
                throw OfferUnavailableException::notFound($item->offerId);
            }

            $currency ??= $offer->currency->value;

            // The colour must be one the product still offers; its swatch is
            // taken from the catalogue, never from the client.
            $color = null;
            if ($item->color !== null && trim($item->color) !== '') {
                $color = collect($offer->colors)->first(fn (array $c) => mb_strtolower($c['name']) === mb_strtolower(trim($item->color)));

                if ($color === null) {
                    throw OfferUnavailableException::colorUnavailable($offer->offerId, $item->color);
                }
            }

            $lines[] = [
                'offer_id' => $offer->offerId,
                'vendor_id' => $offer->vendorId,
                'product_name' => $offer->productName,
                'brand' => $offer->brand,
                'color_name' => $color['name'] ?? null,
                'color_hex' => $color['hex'] ?? null,
                'unit_price_amount' => $offer->priceAmount,
                'currency' => $offer->currency->value,
                'quantity' => $item->quantity,
                'line_total_amount' => $offer->priceAmount * $item->quantity,
            ];
        }

        $subtotal = array_sum(array_column($lines, 'line_total_amount'));

        return DB::transaction(function () use ($lines, $delivery, $subtotal, $currency, $customerId, $couponCode, $channel, $soldBy, $paymentMethod) {
            $orderNumber = $this->generateOrderNumber();

            // Redeemed inside this transaction: if anything below fails the
            // coupon use rolls back with the order. The discount is borne by
            // TechLium - sub-orders (and so vendor payouts) stay at list price.
            $discount = 0;
            $appliedCode = null;

            if ($couponCode !== null && trim($couponCode) !== '') {
                $quote = $this->coupons->redeem($couponCode, $subtotal, trim($delivery->customerPhone), $orderNumber);

                if (! $quote->valid) {
                    throw CouponRejectedException::because((string) $quote->reason);
                }

                $discount = $quote->discountAmount;
                $appliedCode = $quote->code;
            }

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'customer_name' => $delivery->customerName,
                'customer_phone' => $delivery->customerPhone,
                'customer_email' => $delivery->customerEmail,
                'customer_locale' => $delivery->locale,
                'delivery_address' => $delivery->address,
                'delivery_city' => $delivery->city,
                'notes' => $delivery->notes,
                'payment_method' => $paymentMethod,
                'channel' => $channel,
                'sold_by' => $soldBy,
                'status' => OrderStatus::Pending,
                // What the customer owes: every downstream amount (payment,
                // invoice, refunds, reports) is based on this net figure.
                'subtotal_amount' => $subtotal - $discount,
                'discount_amount' => $discount,
                'coupon_code' => $appliedCode,
                'currency' => $currency,
                'delivery_code' => sprintf('%06d', random_int(0, 999_999)),
            ]);

            $this->statusLog->record($order, OrderStatus::Pending->value);

            // One sub-order per seller (vendor_id null = TechLium's own
            // stock), each with its own status and delivery (cahier §3).
            $groups = [];
            foreach ($lines as $line) {
                $groups[$line['vendor_id'] ?? 'own'][] = $line;
            }

            foreach ($groups as $group) {
                $subOrder = $order->subOrders()->create([
                    'vendor_id' => $group[0]['vendor_id'],
                    'status' => OrderStatus::Pending,
                    'subtotal_amount' => array_sum(array_column($group, 'line_total_amount')),
                    'currency' => $currency,
                ]);

                $this->statusLog->recordParcel($subOrder, OrderStatus::Pending->value);

                $subOrder->items()->createMany(array_map(fn (array $line) => $line + ['order_id' => $order->id], $group));
            }

            // The online shop takes any quantity of an active product (what the stock does not
            // cover is a backorder); a counter sale needs the goods on the shelf.
            $taken = [];
            foreach ($lines as $line) {
                try {
                    $units = $this->catalog->decrementStock($line['offer_id'], $line['quantity'], $channel === 'online');
                } catch (InsufficientStockException) {
                    throw OfferUnavailableException::insufficientStock($line['offer_id']);
                }

                if ($units > 0) {
                    $taken[] = ['offer_id' => $line['offer_id'], 'quantity' => $units];
                }
            }

            // The stock is out: Inventory records it in each product's movement history (what actually left the shelf).
            event(new StockSold($order->order_number, $channel, $soldBy, $taken));

            // Raised inside the transaction: a rolled-back order sends nothing. A counter sale
            // texts nobody: the customer is standing at the till and has no delivery code to use.
            if ($channel === 'online') {
                event(new OrderPlaced(
                    $order->order_number, $order->customer_name, $order->customer_phone,
                    $order->subtotal_amount, $order->currency, $order->delivery_code, $order->customer_email, $order->customer_locale ?? 'fr',
                ));
            }

            return $order->load('items', 'subOrders.items', 'subOrders.statusEvents', 'statusEvents');
        });
    }

    private function generateOrderNumber(): string
    {
        return 'TCH-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}
