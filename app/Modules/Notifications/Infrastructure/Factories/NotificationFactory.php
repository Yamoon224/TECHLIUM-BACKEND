<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Infrastructure\Factories;

use App\Modules\Notifications\Domain\NotificationStatus;
use App\Modules\Notifications\Infrastructure\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $reference = 'TCH-'.fake()->numerify('########');

        return [
            'dedupe_key' => 'order.confirmed:'.$reference.':sms',
            'event' => 'order.confirmed',
            'channel' => 'sms',
            'recipient' => fake()->numerify('07########'),
<<<<<<< HEAD
            'body' => 'TechLium : votre commande est confirmée.',
=======
            'body' => 'TechniLium : votre commande est confirmée.',
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
            'reference' => $reference,
            'status' => NotificationStatus::Sent,
            'attempts' => 1,
            'sent_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => NotificationStatus::Failed, 'error' => 'Timeout', 'sent_at' => null,
        ]);
    }
}
