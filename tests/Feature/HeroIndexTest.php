<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated receives a paginated list of their heroes', function ($perPage) {
    $user = User::factory()->create();

    Hero::factory(5)->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/heroes?per_page={$perPage}");

    $response->assertStatus(200)
        ->assertJsonCount($perPage ?? 10, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'hero_class',
                    'level',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ],
            'message',
            'links',
            'meta',
        ]);
})->with([
    'one hero per page' => 1,
    'five heroes per page' => 5,
    'null uses the default' => null,
    'empty uses the default' => '',
]);