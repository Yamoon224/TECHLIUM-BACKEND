<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\Data;

/**
 * Orders' public read-only view of one order line - lets Invoicing snapshot
 * a sale without touching Orders' Eloquent models (cahier §9.1).
 */
final readonly class OrderLineData
{
    public function __construct(
        public string $description,
        public int $quantity,
        public int $unitPriceAmount,
        public int $lineTotalAmount,
<<<<<<< HEAD
        /** Null for TechLium's own stock (1P). */
=======
        /** Null for TechniLium's own stock (1P). */
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
        public ?int $vendorId = null,
        /** The offer sold - lets Returns check and restock by offer. */
        public ?int $offerId = null,
    ) {}
}
