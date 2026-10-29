<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receives a paginated list of only their heroes', function ($perPage) {
    $user = User::factory()->create();

    Hero::factory(10)->create([
        'user_id' => $user->id,
    ]);

    $otherUser = User::factory()->create();

    Hero::factory(3)->create([
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/heroes?per_page={$perPage}");

    $response->assertStatus(200)
        ->assertJsonCount(
            $perPage === null || $perPage === '' ? 10 : $perPage, 
            'data'
        )
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'hero_class',
                    'level',
                    'status',
                    'created_at',
                    'updated_at',
                    'user_id',
                ],
            ],
            'message',
            'links',
            'meta',
        ]);
    
    expect(
        collect($response->json('data'))
            ->pluck('user_id')
            ->unique()
            ->all()
        )->toBe([$user->id]);
        
})->with([
    'one hero per page' => 1,
    'five heroes per page' => 5,
    'null uses the default' => null,
    'empty uses the default' => '',
]);

test('authenticated user without heroes retrieves an empty list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/heroes");

    $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('message', 'Heroes retrieved successfully.');
});

test('unauthenticated user cannot retrieve information', function () {

    $response = $this->getJson('/api/heroes');

    $response->assertUnauthorized();
});

test('invalid per_page values are rejected', function($perPage) {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/heroes?per_page={$perPage}");
    
    $response->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');
})->with([
    'above maximum' => 101,
    'zero' => 0,
    'bellow minimum' => -1,
    'non-numeric value' => 'abc',
]);
