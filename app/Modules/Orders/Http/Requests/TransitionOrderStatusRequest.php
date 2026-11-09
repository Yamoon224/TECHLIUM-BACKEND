<?php

declare(strict_types=1);

namespace App\Modules\Orders\Http\Requests;

use App\Modules\Orders\Domain\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TransitionOrderStatusRequest extends FormRequest
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
            'status' => ['required', new Enum(OrderStatus::class)],
            // Who carries the parcel (the backoffice requires it when shipping a whole order).
            'courier_name' => ['nullable', 'string', 'max:255'],
            'courier_phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function targetStatus(): OrderStatus
    {
        return OrderStatus::from($this->string('status')->toString());
    }
}
