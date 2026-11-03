<?php

use App\Models\User;
use App\Models\Quest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receives a paginated list of the quests', function($perPage) {
    $user = User::factory()->create();

    Quest::factory(10)->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests?per_page={$perPage}");
    
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Quests retrieved successfully.',
        ])
        ->assertJsonCount(
            $perPage === null || $perPage === '' ? 10 : $perPage,
            'data'
        )
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'boss',
                    'difficulty',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'message',
            'links',
            'meta',
        ]);
})->with([
    'one quest per page' => 1,
    'five quests per page' => 5,
    'null uses the default' => null,
    'empty uses the default' => '',
]);

test('authenticated user can retrieve an empty quest list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests");

    $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0)
            ->assertJsonPath('message', 'Quests retrieved successfully.');
});

test('unauthenticated user cannot retrieve information', function () {

    $response = $this->getJson('/api/quests');

    $response->assertUnauthorized();
});

test('invalid per_page values are rejected', function($perPage) {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests?per_page={$perPage}");
    
    $response->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');
})->with([
    'above maximum' => 101,
    'zero' => 0,
    'bellow minimum' => -1,
    'non-numeric value' => 'abc',
]);

test('authenticated user do not receive deactivated heroes', function() {
    $user = User::factory()->create();

    $activeQuest = Quest::factory()->create();

    $deactivatedQuest = Quest::factory()->create([
        'is_active' => false,
    ]);

    $this->assertDatabaseHas('quests', [
        'id' => $deactivatedQuest->id,
        'is_active' => false,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('message', 'Quests retrieved successfully.')
        ->assertJsonPath('data.0.id', $activeQuest->id);
});
