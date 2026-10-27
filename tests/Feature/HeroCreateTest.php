<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can create a new hero', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/heroes', [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',
            
        ]);

    $response->assertCreated();

    $response->assertJson([
        'data' => [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',
            'level' => 1,
            'status' => 'available',
        ],
        'message' => 'Hero created successfully.'
    ]);

    $this->assertDatabaseHas('heroes', [
        'name' => 'Hero Example',
        'hero_class' => 'cleric',
        'user_id' => $user->id
    ]);

});
