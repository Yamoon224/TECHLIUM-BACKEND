<?php

declare(strict_types=1);

namespace App\Modules\Customers\Http\Controllers;

use App\Modules\Customers\Infrastructure\Models\ShopSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use OpenApi\Attributes as OA;

/**
 * What the public help page needs from the server: the figures that must stay
 * true (return window, first-response target) and the contact channels the
 * company has configured. Nothing personal, no authentication.
 */
class HelpController extends Controller
{
    #[OA\Get(
        path: '/help',
        summary: "Informations publiques pour la page d'aide : délai de retour, délai de réponse, contacts",
        tags: ['Support'],
        responses: [new OA\Response(response: 200, description: 'Informations publiques')],
    )]
    public function show(): JsonResponse
    {
        $shop = ShopSetting::current();

        return response()->json([
            'return_window_days' => (int) config('returns.window_days'),
            'first_response_hours' => (int) config('support.first_response_hours.normal'),
            // Only what is configured: an empty channel is not advertised.
            'contact' => array_filter([
                'phone' => $shop->contact('phone'),
                'whatsapp' => $shop->contact('whatsapp'),
                'email' => $shop->contact('email'),
                'hours' => $shop->contact('hours'),
            ], fn ($value) => trim((string) $value) !== ''),
        ]);
    }
}
