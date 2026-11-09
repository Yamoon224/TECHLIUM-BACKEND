<?php

declare(strict_types=1);

use App\Modules\Catalog\Infrastructure\Models\Offer;
use App\Modules\Catalog\Infrastructure\Models\Product;
use App\Modules\Catalog\Infrastructure\Models\ProductVariant;
use App\Modules\Identity\Domain\Role;
use App\Modules\Identity\Infrastructure\Models\User;
use App\Modules\Inventory\Infrastructure\Models\StockMovement;
use Spatie\Permission\Models\Role as RoleModel;

beforeEach(function () {
    foreach (Role::cases() as $role) {
        RoleModel::findOrCreate($role->value, 'web');
    }
});

function stockManager(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::GestionnaireStock->value);

    return $user;
}

function offerWithStock(int $quantity): Offer
{
    $variant = ProductVariant::factory()->for(Product::factory())->create();

    return Offer::factory()->create([
        'product_variant_id' => $variant->id,
        'stock_quantity' => $quantity,
    ]);
}

it('rejects an anonymous request to list stock movements', function () {
    $this->getJson('/api/v1/admin/stock/movements')->assertUnauthorized();
});

it('rejects roles other than gestionnaire_stock from managing inventory', function (Role $role) {
    $user = User::factory()->create();
    $user->assignRole($role->value);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/admin/stock/movements')
        ->assertForbidden();
})->with([
    Role::VendeurTiers,
    Role::GestionnaireCatalogue,
    Role::OperateurCommandes,
    Role::Client,
]);

it('records a positive stock movement and increases the offer\'s stock', function () {
    $offer = offerWithStock(10);

    $response = $this->actingAs(stockManager(), 'sanctum')
        ->postJson('/api/v1/admin/stock/movements', [
            'offer_id' => $offer->id,
            'quantity_delta' => 15,
            'reason' => 'Réception fournisseur',
        ]);

    $response->assertCreated();
    $response->assertJsonPath('quantity_delta', 15);
    $response->assertJsonPath('quantity_after', 25);
    expect($offer->fresh()->stock_quantity)->toBe(25);
});

it('records a negative stock movement and decreases the offer\'s stock', function () {
    $offer = offerWithStock(10);

    $response = $this->actingAs(stockManager(), 'sanctum')
        ->postJson('/api/v1/admin/stock/movements', [
            'offer_id' => $offer->id,
            'quantity_delta' => -4,
            'reason' => 'Casse',
        ]);

    $response->assertCreated();
    $response->assertJsonPath('quantity_after', 6);
    expect($offer->fresh()->stock_quantity)->toBe(6);
});

it('refuses a movement that would drive stock negative', function () {
    $offer = offerWithStock(3);

    $response = $this->actingAs(stockManager(), 'sanctum')
        ->postJson('/api/v1/admin/stock/movements', [
            'offer_id' => $offer->id,
            'quantity_delta' => -10,
        ]);

    $response->assertStatus(422);
    expect($offer->fresh()->stock_quantity)->toBe(3);
    expect(StockMovement::query()->where('offer_id', $offer->id)->exists())->toBeFalse();
});

it('rejects a zero-quantity movement', function () {
    $offer = offerWithStock(10);

    $this->actingAs(stockManager(), 'sanctum')
        ->postJson('/api/v1/admin/stock/movements', ['offer_id' => $offer->id, 'quantity_delta' => 0])
        ->assertUnprocessable();
});

it('rejects a movement for an unknown offer', function () {
    $this->actingAs(stockManager(), 'sanctum')
        ->postJson('/api/v1/admin/stock/movements', ['offer_id' => 999999, 'quantity_delta' => 5])
        ->assertUnprocessable();
});

it('filters movement history by offer', function () {
    $offerA = offerWithStock(10);
    $offerB = offerWithStock(10);
    StockMovement::factory()->create(['offer_id' => $offerA->id]);
    StockMovement::factory()->create(['offer_id' => $offerB->id]);

    $response = $this->actingAs(stockManager(), 'sanctum')
        ->getJson("/api/v1/admin/stock/movements?offer_id={$offerA->id}");

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.offer_id', $offerA->id);
});

it('lists offers at or below the given stock threshold', function () {
    $low = offerWithStock(2);
    offerWithStock(50);

    $response = $this->actingAs(stockManager(), 'sanctum')
        ->getJson('/api/v1/admin/stock/low-stock?threshold=5');

    $response->assertOk();
    expect($response->json())->toHaveCount(1);
    $response->assertJsonPath('0.offer_id', $low->id);
});

it('lists the published offers of active products only, with their stock', function () {
    $active = offerWithStock(12);
    $inactive = offerWithStock(7);
    $inactive->variant->product->update(['status' => 'draft']);

    $response = $this->actingAs(stockManager(), 'sanctum')->getJson('/api/v1/admin/stock/products')->assertOk();

    expect(collect($response->json())->pluck('stock_quantity', 'offer_id')->all())->toBe([$active->id => 12]);
});

it('keeps the movement history of one offer', function () {
    $offer = offerWithStock(10);
    $other = offerWithStock(10);
    $staff = stockManager();

    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/admin/stock/movements', ['offer_id' => $offer->id, 'quantity_delta' => 5, 'reason' => 'Réception'])->assertCreated();
    $this->actingAs($staff, 'sanctum')->postJson('/api/v1/admin/stock/movements', ['offer_id' => $other->id, 'quantity_delta' => -2])->assertCreated();

    $this->actingAs($staff, 'sanctum')->getJson('/api/v1/admin/stock/movements?offer_id='.$offer->id)
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.quantity_after', 15);
});

it('records the stock taken out by an online order in the offer history', function () {
    $offer = offerWithStock(10);

    $order = $this->postJson('/api/v1/orders', [
        'customer_name' => 'Awa Traoré',
        'customer_phone' => '+2250700000000',
        'delivery_address' => 'Cocody',
        'items' => [['offer_id' => $offer->id, 'quantity' => 3]],
    ])->assertCreated();

    expect($offer->fresh()->stock_quantity)->toBe(7);

    $movement = StockMovement::query()->where('offer_id', $offer->id)->sole();
    expect($movement->quantity_delta)->toBe(-3)
        ->and($movement->quantity_after)->toBe(7)
        ->and($movement->reason)->toBe('Commande '.$order->json('order_number'));
});

it('filters the movement history by period', function () {
    $offer = offerWithStock(10);
    $old = StockMovement::factory()->create(['offer_id' => $offer->id, 'created_at' => '2025-01-10 10:00:00']);
    $recent = StockMovement::factory()->create(['offer_id' => $offer->id, 'created_at' => '2025-03-10 10:00:00']);

    $ids = fn (array $query) => collect($this->actingAs(stockManager(), 'sanctum')->getJson('/api/v1/admin/stock/movements?'.http_build_query(['offer_id' => $offer->id] + $query))->assertOk()->json('data'))->pluck('id')->all();

    expect($ids(['from' => '2025-02-01']))->toBe([$recent->id])
        ->and($ids(['to' => '2025-02-01']))->toBe([$old->id])
        ->and($ids(['from' => '2025-01-10', 'to' => '2025-03-10']))->toHaveCount(2);
});
