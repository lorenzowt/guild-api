<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\HeroStatus;

uses(RefreshDatabase::class);

test('authenticated user can deactivate their active hero', function ($status) {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => $status,
    ]);

    $response = $this->actingAs($user, 'api')
        ->deleteJson("/api/heroes/$hero->id");

    $response->assertStatus(200)    
        ->assertJson([
                'message' => 'Hero deleted successfully.'
            ]);


    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Deactivated->value,
    ]);
})->with([
    'available status' => HeroStatus::Available->value,
    'injured status' => HeroStatus::Injured->value,
    'on mission status' => HeroStatus::OnMission->value,
]);

test('authenticated user cannot deactivate a deactivated hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Deactivated->value,
    ]);

    $response = $this->deleteJson("/api/heroes/$hero->id");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Deactivated->value,
    ]);
});

test('unauthenticated user cannot deactivate their hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->deleteJson("/api/heroes/$hero->id");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Available->value,
    ]);
});

test('authenticated user cannot deactivate hero they do not own', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $anotherUser = User::factory()->create();

    $response = $this->actingAs($anotherUser, 'api')
        ->deleteJson("/api/heroes/$hero->id");

    $response->assertNotFound();

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Available->value,
    ]);
});