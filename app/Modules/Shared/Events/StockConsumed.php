<?php

declare(strict_types=1);

namespace App\Modules\Shared\Events;

/**
<<<<<<< HEAD
 * Published by Procurement when delivered goods from TechLium's own stock
=======
 * Published by Procurement when delivered goods from TechniLium's own stock
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
 * leave it at their average cost; Accounting books the cost of goods sold.
 */
final readonly class StockConsumed implements DomainEvent
{
    public function __construct(
        public int $orderId,
        public string $orderNumber,
        public int $costAmount,
        public string $currency,
    ) {}
}
