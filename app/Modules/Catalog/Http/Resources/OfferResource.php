<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Resources;

use App\Modules\Catalog\Infrastructure\Models\Offer;
use App\Modules\Shared\ValueObjects\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Offer $offer */
        $offer = $this->resource;

        return [
            'id' => $offer->id,
<<<<<<< HEAD
            'seller' => $offer->isSoldByTechLium() ? 'TechLium' : 'vendor',
=======
            'seller' => $offer->isSoldByTechniLium() ? 'TechniLium' : 'vendor',
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
            'vendor_id' => $offer->vendor_id,
            'price' => $this->money($offer->price()),
            'compare_at_price' => ($compareAt = $offer->compareAtPrice()) ? $this->money($compareAt) : null,
            'condition_grade' => $offer->condition_grade,
            'warranty_months' => $offer->warranty_months,
            'stock_quantity' => $offer->stock_quantity,
            'in_stock' => $offer->isInStock(),
            'status' => $offer->status->value,
        ];
    }

    /**
     * @return array{amount: int, currency: string}
     */
    private function money(Money $money): array
    {
        return [
            'amount' => $money->amount,
            'currency' => $money->currency->value,
        ];
    }
}
