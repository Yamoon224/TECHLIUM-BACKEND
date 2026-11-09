<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Listeners;

use App\Modules\Catalog\Application\Contracts\InventoryGatewayInterface;
use App\Modules\Inventory\Infrastructure\Models\StockMovement;
use App\Modules\Shared\Events\StockSold;

/**
 * Writes the stock taken out by an order (online) or a counter sale (internal)
 * into the movement history. The quantity itself was already decremented by
 * the checkout, atomically with the order: this only records it, with the level
 * the offer was left at, so history and stock always agree.
 */
final class RecordSoldStock
{
    public function __construct(private readonly InventoryGatewayInterface $inventory) {}

    public function handle(StockSold $event): void
    {
        $reason = ($event->channel === 'internal' ? 'Vente interne ' : 'Commande ').$event->orderNumber;

        foreach ($event->lines as $line) {
            $offer = $this->inventory->find($line['offer_id']);

            if ($offer === null) {
                continue;
            }

            StockMovement::query()->create([
                'offer_id' => $line['offer_id'],
                'created_by' => $event->soldBy,
                'quantity_delta' => -$line['quantity'],
                'quantity_after' => $offer->stockQuantity,
                'reason' => $reason,
            ]);
        }
    }
}
