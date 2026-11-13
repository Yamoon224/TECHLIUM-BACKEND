<?php

declare(strict_types=1);

namespace App\Modules\Customers\Http\Controllers;

use App\Modules\Customers\Http\Requests\UpdateShopSettingsRequest;
use App\Modules\Customers\Infrastructure\Models\ShopSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

/** Back-office editing of the shop's public information. Gated by can:manage-catalog. */
class ShopAdminController extends Controller
{
    #[OA\Get(
        path: '/admin/shop',
        summary: 'Informations de la boutique, telles que saisies (français et anglais)',
        security: [['sanctum' => []]],
        tags: ['Support'],
        responses: [new OA\Response(response: 200, description: 'Informations de la boutique')],
    )]
    public function show(): JsonResponse
    {
        return response()->json($this->payload(ShopSetting::current()));
    }

    #[OA\Put(
        path: '/admin/shop',
        summary: 'Met à jour les informations de la boutique',
        security: [['sanctum' => []]],
        tags: ['Support'],
        responses: [
            new OA\Response(response: 200, description: 'Informations mises à jour'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ],
    )]
    public function update(UpdateShopSettingsRequest $request): JsonResponse
    {
        $shop = ShopSetting::current();
        $data = $request->validated();

        // An emptied field is stored as null, not as an empty string.
        foreach (['phone', 'whatsapp', 'email', 'hours', 'address', 'tiktok_url', 'delivery_info', 'delivery_info_en'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = trim((string) $data[$field]) === '' ? null : trim((string) $data[$field]);
            }
        }

        if (array_key_exists('topbar_messages', $data)) {
            $data['topbar_messages'] = collect($data['topbar_messages'] ?? [])
                ->map(fn (array $m) => ['fr' => trim($m['fr']), 'en' => trim((string) ($m['en'] ?? '')) ?: null])
                ->values()
                ->all() ?: null;
        }

        $shop->fill($data)->save();

        return response()->json($this->payload($shop->fresh()));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ShopSetting $shop): array
    {
        return [
            'phone' => $shop->phone,
            'whatsapp' => $shop->whatsapp,
            'email' => $shop->email,
            'hours' => $shop->hours,
            'address' => $shop->address,
            'latitude' => $shop->latitude,
            'longitude' => $shop->longitude,
            'tiktok_url' => $shop->tiktok_url,
            'delivery_info' => $shop->delivery_info,
            'delivery_info_en' => $shop->delivery_info_en,
            'topbar_messages' => $shop->topbar_messages ?? [],
            // null in the database means "all"; the editor always sees the explicit list.
            'payment_methods' => $shop->payment_methods ?? ShopSetting::PAYMENT_METHODS,
            'available_payment_methods' => ShopSetting::PAYMENT_METHODS,
        ];
    }
}
