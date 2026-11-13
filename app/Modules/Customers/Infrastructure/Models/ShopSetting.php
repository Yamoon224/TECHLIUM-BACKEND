<?php

declare(strict_types=1);

namespace App\Modules\Customers\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The shop's public information (contacts, address, map point, social page,
 * delivery wording, scrolling bar, means of payment). There is one row.
 *
 * @property int $id
 * @property string|null $phone
 * @property string|null $whatsapp
 * @property string|null $email
 * @property string|null $hours
 * @property string|null $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $tiktok_url
 * @property string|null $delivery_info
 * @property string|null $delivery_info_en
 * @property list<array{fr: string, en: string|null}>|null $topbar_messages
 * @property list<string>|null $payment_methods
 */
class ShopSetting extends Model
{
    /** Every means of payment the footer knows how to draw, in display order. */
    public const PAYMENT_METHODS = ['mtn', 'wave', 'cash', 'visa', 'mastercard', 'orange'];

    protected $fillable = [
        'phone', 'whatsapp', 'email', 'hours', 'address', 'latitude', 'longitude', 'tiktok_url',
        'delivery_info', 'delivery_info_en', 'topbar_messages', 'payment_methods',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'topbar_messages' => 'array',
            'payment_methods' => 'array',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }

    /**
     * A contact channel: what was entered in the back office, else what the
     * environment configures (the old way), else nothing.
     */
    public function contact(string $channel): string
    {
        $own = trim((string) $this->getAttribute($channel));

        return $own !== '' ? $own : trim((string) config("support.contact.{$channel}"));
    }
}
