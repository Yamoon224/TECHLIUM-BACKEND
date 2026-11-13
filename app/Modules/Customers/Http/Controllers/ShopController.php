<?php

declare(strict_types=1);

namespace App\Modules\Customers\Http\Controllers;

use App\Modules\Customers\Infrastructure\Models\ShopSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

/** The shop's public information, for the storefront's top bar, footer, map and assistant. */
class ShopController extends Controller
{
    #[OA\Get(
        path: '/shop',
        summary: 'Informations publiques de la boutique : contacts, adresse, carte, TikTok, livraison, barre défilante, paiements',
        tags: ['Support'],
        responses: [new OA\Response(response: 200, description: 'Informations de la boutique')],
    )]
    public function show(Request $request): JsonResponse
    {
        $shop = ShopSetting::current();
        $english = $request->query('lang') === 'en';

        // The French text is the reference; English is used only when written.
        $pick = fn (?string $fr, ?string $en): ?string => $english && trim((string) $en) !== '' ? $en : $fr;
        $filled = fn (?string $text): ?string => trim((string) $text) !== '' ? $text : null;

        $messages = collect($shop->topbar_messages ?? [])
            ->map(fn (array $message) => $pick($message['fr'] ?? null, $message['en'] ?? null))
            ->filter(fn ($text) => trim((string) $text) !== '')
            ->values()
            ->all();

        $hasPoint = $shop->latitude !== null && $shop->longitude !== null;

        return response()->json([
            // Only what is filled in: an empty channel is not advertised.
            'contact' => array_filter([
                'phone' => $shop->contact('phone'),
                'whatsapp' => $shop->contact('whatsapp'),
                'email' => $shop->contact('email'),
                'hours' => $shop->contact('hours'),
            ], fn (string $value) => $value !== ''),
            'address' => $filled($shop->address),
            'location' => $hasPoint ? ['latitude' => $shop->latitude, 'longitude' => $shop->longitude] : null,
            'tiktok_url' => $filled($shop->tiktok_url),
            'delivery_info' => $filled($pick($shop->delivery_info, $shop->delivery_info_en)),
            'topbar_messages' => $messages,
            'payment_methods' => $shop->payment_methods ?? ShopSetting::PAYMENT_METHODS,
        ]);
    }
}
