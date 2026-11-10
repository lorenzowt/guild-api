<?php
namespace Tests\Feature\Hero;

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\HeroStatus;

uses(RefreshDatabase::class);

test('authenticated user can heal an injured hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Injured->value,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/heroes/$hero->id/heal");

    $response->assertStatus(200)    
        ->assertJson([
                'message' => 'Hero healed successfully.'
            ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Available->value,
    ]);
});

test('authenticated user canot heal a hero that is not injured', function ($status) {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => $status,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/heroes/$hero->id/heal");

    $response->assertStatus(409)    
        ->assertJson([
                'message' => "Hero isn't injured."
            ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => $status,
    ]);
})->with([
    'available status' => HeroStatus::Available->value,
    'on_mission status' => HeroStatus::OnMission->value,
]);

test('authenticated user cannot heal a deactivated hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Deactivated->value,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/heroes/$hero->id/heal");

    $response->assertNotFound();

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Deactivated->value,
    ]);
});

test('authenticated user cannot heal a hero they do not own', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Injured->value,
    ]);

    $anotherUser = User::factory()->create();

    $response = $this->actingAs($anotherUser, 'api')
        ->postJson("/api/heroes/$hero->id/heal");

    $response->assertNotFound();

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Injured->value,
    ]);
});

test('unauthenticated user cannot heal their hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Injured->value,
    ]);

    $response = $this->postJson("/api/heroes/$hero->id/heal");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Injured->value,
    ]);
});