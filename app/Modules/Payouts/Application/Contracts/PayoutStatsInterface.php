<?php

declare(strict_types=1);

namespace App\Modules\Payouts\Application\Contracts;

use DateTimeInterface;

/** Payouts' public read-model for Reporting (commissions, vendor debt). */
interface PayoutStatsInterface
{
<<<<<<< HEAD
    /** What TechLium still owes vendors: net of payouts calculated but not yet paid. */
=======
    /** What TechniLium still owes vendors: net of payouts calculated but not yet paid. */
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
    public function owedToVendors(): int;

    /** Commission earned on payouts calculated in [from, to]. */
    public function commissionsBetween(DateTimeInterface $from, DateTimeInterface $to): int;

    /** Net amounts actually paid to vendors in [from, to]. */
    public function paidBetween(DateTimeInterface $from, DateTimeInterface $to): int;
}
