<?php

namespace Tests\Feature\Quest;

use App\Enums\UserRole;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can update selected quest fields', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $quest = Quest::factory()->create([
        'title' => 'Defeat the Ancient Dragon',
        'description' => 'The dragon is terrorizing the northern villages.',
        'boss' => 'Ancient Dragon',
        'difficulty' => 8,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/quests/{$quest->id}", [
            'difficulty' => 10,
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $quest->id,
                'title' => 'Defeat the Ancient Dragon',
                'description' => 'The dragon is terrorizing the northern villages.',
                'boss' => 'Ancient Dragon',
                'difficulty' => 10,
                'is_active' => true,
            ],
            'message' => 'Quest updated successfully.',
        ]);

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'title' => 'Defeat the Ancient Dragon',
        'difficulty' => 10,
        'is_active' => true,
    ]);
});

test('admin cannot update a quest with invalid data', function (array $payload, string $field) {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $quest = Quest::factory()->create();

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/quests/{$quest->id}", $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'title' => $quest->title,
        'description' => $quest->description,
        'boss' => $quest->boss,
        'difficulty' => $quest->difficulty,
    ]);
})->with([
    'empty title' => [['title' => ''], 'title'],
    'title is too long' => [['title' => str_repeat('a', 101)], 'title'],
    'title has invalid type' => [['title' => 123], 'title'],

    'empty description' => [['description' => ''], 'description'],
    'description has invalid type' => [['description' => ['text']], 'description'],

    'empty boss' => [['boss' => ''], 'boss'],
    'boss is too long' => [['boss' => str_repeat('a', 46)], 'boss'],
    'boss has invalid type' => [['boss' => ['name']], 'boss'],

    'difficulty is below minimum' => [['difficulty' => 0], 'difficulty'],
    'difficulty is above maximum' => [['difficulty' => 11], 'difficulty'],
    'difficulty has invalid type' => [['difficulty' => 'hard'], 'difficulty'],
]);

test('admin cannot update a quest without providing an updatable field', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $quest = Quest::factory()->create();

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/quests/{$quest->id}", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['request']);
});

test('admin cannot override quest creator or active status when updating a quest', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $anotherUser = User::factory()->create();

    $quest = Quest::factory()->create([
        'created_by' => $admin->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/quests/{$quest->id}", [
            'title' => 'Defeat the Elder Dragon',
            'created_by' => $anotherUser->id,
            'is_active' => false,
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'title' => 'Defeat the Elder Dragon',
                'is_active' => true,
            ],
        ]);

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'title' => 'Defeat the Elder Dragon',
        'created_by' => $admin->id,
        'is_active' => true,
    ]);
});

test('regular user cannot update a quest', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/quests/{$quest->id}", [
            'difficulty' => 10,
        ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot update a quest', function () {
    $quest = Quest::factory()->create();

    $response = $this->patchJson("/api/quests/{$quest->id}", [
        'difficulty' => 10,
    ]);

    $response->assertUnauthorized();
});

test('admin receives not found when updating a nonexistent quest', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson('/api/quests/999', [
            'difficulty' => 10,
        ]);

    $response->assertNotFound();
});