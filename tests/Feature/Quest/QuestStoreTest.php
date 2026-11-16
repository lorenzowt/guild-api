<?php

namespace Tests\Feature\Quest;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

test('admin can create a new active quest', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->postJson('/api/quests', [
            'title' => 'Defeat the Ancient Dragon',
            'description' => 'A dragon is terrorizing the northern villages.',
            'boss' => 'Ancient Dragon',
            'difficulty' => 8,
        ]);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'title' => 'Defeat the Ancient Dragon',
                'description' => 'A dragon is terrorizing the northern villages.',
                'boss' => 'Ancient Dragon',
                'difficulty' => 8,
                'is_active' => true,
            ],
            'message' => 'Quest created successfully.',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'boss',
                'difficulty',
                'is_active',
                'created_at',
                'updated_at',
            ],
            'message',
        ]);

    $this->assertDatabaseHas('quests', [
        'title' => 'Defeat the Ancient Dragon',
        'description' => 'A dragon is terrorizing the northern villages.',
        'boss' => 'Ancient Dragon',
        'difficulty' => 8,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);
});

test('admin cannot create a quest with invalid data', function (array $payload, string $field) {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->postJson('/api/quests', array_merge([
            'title' => 'Defeat the Ancient Dragon',
            'description' => 'A dragon is terrorizing the northern villages.',
            'boss' => 'Ancient Dragon',
            'difficulty' => 8,
        ], $payload));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);

    $this->assertDatabaseCount('quests', 0);
})->with([
    'missing title' => [['title' => null], 'title'],
    'empty title' => [['title' => ''], 'title'],
    'title is too long' => [['title' => str_repeat('a', 101)], 'title'],
    'title has invalid type' => [['title' => 123], 'title'],

    'missing description' => [['description' => null], 'description'],
    'empty description' => [['description' => ''], 'description'],
    'description has invalid type' => [['description' => ['text']], 'description'],

    'missing boss' => [['boss' => null], 'boss'],
    'empty boss' => [['boss' => ''], 'boss'],
    'boss is too long' => [['boss' => str_repeat('a', 46)], 'boss'],
    'boss has invalid type' => [['boss' => ['name']], 'boss'],

    'missing difficulty' => [['difficulty' => null], 'difficulty'],
    'difficulty is below minimum' => [['difficulty' => 0], 'difficulty'],
    'difficulty is above maximum' => [['difficulty' => 11], 'difficulty'],
    'difficulty has invalid type' => [['difficulty' => 'hard'], 'difficulty'],
]);

test('admin cannot override quest creator or active status', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $anotherUser = User::factory()->create();

    $response = $this->actingAs($admin, 'api')
        ->postJson('/api/quests', [
            'title' => 'Defeat the Ancient Dragon',
            'description' => 'A dragon is terrorizing the northern villages.',
            'boss' => 'Ancient Dragon',
            'difficulty' => 8,
            'created_by' => $anotherUser->id,
            'is_active' => false,
        ]);

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('quests', [
        'title' => 'Defeat the Ancient Dragon',
        'created_by' => $admin->id,
        'is_active' => true,
    ]);
});

test('regular user cannot create a quest', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/quests', [
            'title' => 'Defeat the Ancient Dragon',
            'description' => 'A dragon is terrorizing the northern villages.',
            'boss' => 'Ancient Dragon',
            'difficulty' => 8,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseCount('quests', 0);
});

test('unauthenticated user cannot create a quest', function () {
    $response = $this->postJson('/api/quests', [
        'title' => 'Defeat the Ancient Dragon',
        'description' => 'A dragon is terrorizing the northern villages.',
        'boss' => 'Ancient Dragon',
        'difficulty' => 8,
    ]);

    $response->assertUnauthorized();

    $this->assertDatabaseCount('quests', 0);
});