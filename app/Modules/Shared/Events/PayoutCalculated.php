<?php

declare(strict_types=1);

namespace App\Modules\Shared\Events;

/**
 * Published by Payouts when a vendor payout is calculated: the vendor's
<<<<<<< HEAD
 * gross share leaves sales revenue, TechLium keeps the commission and owes
=======
 * gross share leaves sales revenue, TechniLium keeps the commission and owes
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
 * the net to the vendor. Accounting turns it into entries.
 */
final readonly class PayoutCalculated implements DomainEvent
{
    public function __construct(
        public int $payoutId,
        public int $vendorId,
        public int $grossAmount,
        public int $commissionAmount,
        public int $netAmount,
        public string $currency,
    ) {}
}
