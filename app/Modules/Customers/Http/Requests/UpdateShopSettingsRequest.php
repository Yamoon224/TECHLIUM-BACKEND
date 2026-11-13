<?php

declare(strict_types=1);

namespace App\Modules\Customers\Http\Requests;

use App\Modules\Customers\Infrastructure\Models\ShopSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'hours' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            // The map point is both coordinates or none.
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'tiktok_url' => ['nullable', 'url:https', 'max:255'],
            'delivery_info' => ['nullable', 'string', 'max:1000'],
            'delivery_info_en' => ['nullable', 'string', 'max:1000'],
            'topbar_messages' => ['nullable', 'array', 'max:8'],
            'topbar_messages.*.fr' => ['required', 'string', 'max:120'],
            'topbar_messages.*.en' => ['nullable', 'string', 'max:120'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['string', Rule::in(ShopSetting::PAYMENT_METHODS), 'distinct'],
        ];
    }
}
