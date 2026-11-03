<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\HeroStatus;

uses(RefreshDatabase::class);

test('authenticated user can deactivate their hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
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

});