<?php

declare(strict_types=1);

use App\Modules\Customers\Infrastructure\Models\ShopSetting;
use App\Modules\Identity\Domain\Role;
use App\Modules\Identity\Infrastructure\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    foreach (Role::cases() as $role) {
        RoleModel::findOrCreate($role->value, 'web');
    }
});

function shopManager(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::GestionnaireCatalogue->value);

    return $user;
}

it('serves the shop information to anyone, with every means of payment by default', function () {
    config(['support.contact' => ['phone' => '', 'whatsapp' => '', 'email' => '', 'hours' => '']]);

    $this->getJson('/api/v1/shop')->assertOk()
        ->assertJsonPath('contact', [])
        ->assertJsonPath('topbar_messages', [])
        ->assertJsonPath('payment_methods', ShopSetting::PAYMENT_METHODS);
});

it('rejects an anonymous or non-staff request to edit', function () {
    $this->putJson('/api/v1/admin/shop', [])->assertUnauthorized();

    $client = User::factory()->create();
    $client->assignRole(Role::Client->value);
    Sanctum::actingAs($client);

    $this->putJson('/api/v1/admin/shop', [])->assertForbidden();
});

it('saves what staff enter and serves it publicly, in the visitor\'s language', function () {
    Sanctum::actingAs(shopManager());

    $this->putJson('/api/v1/admin/shop', [
        'whatsapp' => '+225 01 73 95 58 43',
        'hours' => 'Lun-Sam 8h-19h',
        'address' => 'Abidjan',
        'latitude' => 5.3013244,
        'longitude' => -4.0021515,
        'tiktok_url' => 'https://www.tiktok.com/@techliumgroupe0',
        'delivery_info' => 'Livraison sous 24 h à Abidjan.',
        'delivery_info_en' => 'Delivery within 24 h in Abidjan.',
        'topbar_messages' => [['fr' => 'Livraison offerte', 'en' => 'Free delivery'], ['fr' => 'Garantie 12 mois', 'en' => '']],
        'payment_methods' => ['mtn', 'cash'],
    ])->assertOk();

    $fr = $this->getJson('/api/v1/shop')->assertOk();
    $fr->assertJsonPath('contact.whatsapp', '+225 01 73 95 58 43')
        ->assertJsonPath('location.latitude', 5.3013244)
        ->assertJsonPath('delivery_info', 'Livraison sous 24 h à Abidjan.')
        ->assertJsonPath('topbar_messages', ['Livraison offerte', 'Garantie 12 mois'])
        ->assertJsonPath('payment_methods', ['mtn', 'cash']);

    // English where written, the French text where it is not.
    $this->getJson('/api/v1/shop?lang=en')
        ->assertJsonPath('delivery_info', 'Delivery within 24 h in Abidjan.')
        ->assertJsonPath('topbar_messages', ['Free delivery', 'Garantie 12 mois']);
});

it('feeds the help page contacts from the same settings, falling back to the environment', function () {
    config(['support.contact' => ['phone' => '+225 00', 'whatsapp' => '', 'email' => '', 'hours' => '']]);
    Sanctum::actingAs(shopManager());
    $this->putJson('/api/v1/admin/shop', ['whatsapp' => '+225 11'])->assertOk();

    $this->getJson('/api/v1/help')->assertJsonPath('contact', ['phone' => '+225 00', 'whatsapp' => '+225 11']);
});

it('stores an emptied field as nothing and hides it', function () {
    Sanctum::actingAs(shopManager());
    $this->putJson('/api/v1/admin/shop', ['tiktok_url' => 'https://www.tiktok.com/@x'])->assertOk();
    $this->putJson('/api/v1/admin/shop', ['tiktok_url' => '  '])->assertOk();

    $this->getJson('/api/v1/shop')->assertJsonPath('tiktok_url', null);
});

it('refuses a map point with only one coordinate, an unknown payment method or an unsafe link', function () {
    Sanctum::actingAs(shopManager());

    $this->putJson('/api/v1/admin/shop', ['latitude' => 5.3])->assertUnprocessable()->assertJsonValidationErrors('longitude');
    $this->putJson('/api/v1/admin/shop', ['payment_methods' => ['bitcoin']])->assertUnprocessable();
    $this->putJson('/api/v1/admin/shop', ['tiktok_url' => 'javascript:alert(1)'])->assertUnprocessable();
    $this->putJson('/api/v1/admin/shop', ['topbar_messages' => [['fr' => '']]])->assertUnprocessable();
});
