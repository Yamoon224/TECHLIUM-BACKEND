<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\Contracts;

use DateTimeInterface;

/**
 * Orders' public read-model for Reporting (cahier §6.14). Figures exclude
 * cancelled orders unless stated; amounts are VAT-inclusive minor units.
 */
interface OrderStatsInterface
{
    /**
     * Sales per calendar day in [from, to], oldest first.
     *
     * @return list<array{date: string, orders: int, revenue: int}>
     */
    public function salesByDay(DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * Orders placed in [from, to] per status (cancelled included).
     *
     * @return array<string, int>
     */
    public function ordersByStatus(DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * Best-selling products by revenue in [from, to].
     *
     * @return list<array{product: string, quantity: int, revenue: int}>
     */
    public function topProducts(DateTimeInterface $from, DateTimeInterface $to, int $limit): array;

    /**
<<<<<<< HEAD
     * Revenue per seller in [from, to], by sub-order; vendor_id null = TechLium's own stock.
=======
     * Revenue per seller in [from, to], by sub-order; vendor_id null = TechniLium's own stock.
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
     *
     * @return list<array{vendor_id: int|null, orders: int, revenue: int}>
     */
    public function revenueBySeller(DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * Orders delivered in [from, to] that include at least one line of
<<<<<<< HEAD
     * TechLium's own stock (vendor_id null), with those lines. The order's
     * full list-price total and its coupon discount come along, because the
     * discount is borne by TechLium and must be shared out by the caller.
=======
     * TechniLium's own stock (vendor_id null), with those lines. The order's
     * full list-price total and its coupon discount come along, because the
     * discount is borne by TechniLium and must be shared out by the caller.
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
     *
     * @return list<array{
     *     order_id: int, order_number: string, delivered_on: string,
     *     items_total: int, discount: int,
     *     lines: list<array{offer_id: int, product: string, quantity: int, line_total: int}>
     * }>
     */
    public function deliveredOwnStockSales(DateTimeInterface $from, DateTimeInterface $to): array;

    /** Orders still pending or confirmed that were placed before the given moment. */
    public function countStalledBefore(DateTimeInterface $moment): int;
}
