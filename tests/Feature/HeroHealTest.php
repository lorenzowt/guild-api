<?php

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