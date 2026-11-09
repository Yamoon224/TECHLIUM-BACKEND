<?php

declare(strict_types=1);

namespace App\Modules\Shared\Events;

/**
 * Published by Orders once an order's stock has been taken out of the offers
 * (when the order is placed, online or at the counter): Inventory records each
 * line as an audited movement so the product's history shows the sale.
 */
final readonly class StockSold implements DomainEvent
{
    /**
     * @param  list<array{offer_id: int, quantity: int}>  $lines
     */
    public function __construct(
        public string $orderNumber,
        public string $channel,
        public ?int $soldBy,
        public array $lines,
    ) {}
}
