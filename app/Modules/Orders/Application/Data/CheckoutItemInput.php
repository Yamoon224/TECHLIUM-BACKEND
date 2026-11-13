<?php

declare(strict_types=1);

namespace App\Modules\Orders\Application\Data;

final readonly class CheckoutItemInput
{
    public function __construct(
        public int $offerId,
        public int $quantity,
        /** Name of the colour picked, as listed on the product; null when none. */
        public ?string $color = null,
    ) {}
}
