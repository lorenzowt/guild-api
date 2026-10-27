<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated receives a paginated list of their heroes', function () {
    
    $user = User::factory()->create();

    $heroes = Hero::factory(5)->create([
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user, 'api')->getJson('/api/heroes');

    $response->assertStatus(200)
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['name', 'hero_class', 'level', 'status', 'created_at', 'updated_at']
            ],
            'message',
            'links',
            'meta',
        ]);

});
