<?php

namespace Tests\Feature\Quest;

use App\Enums\UserRole;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can deactivate a quest', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->deleteJson("/api/quests/{$quest->id}");

    $response->assertOk()
        ->assertJson([
            'message' => 'Quest deleted successfully.',
        ]);

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'is_active' => false,
    ]);
});

test('deactivated quest is no longer available to users', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $this->actingAs($admin, 'api')
        ->deleteJson("/api/quests/{$quest->id}")
        ->assertOk();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests/{$quest->id}");

    $response->assertNotFound();
});

test('regular user cannot deactivate a quest', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->actingAs($user, 'api')
        ->deleteJson("/api/quests/{$quest->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'is_active' => true,
    ]);
});

test('unauthenticated user cannot deactivate a quest', function () {
    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->deleteJson("/api/quests/{$quest->id}");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'is_active' => true,
    ]);
});

test('admin receives not found when deactivating a nonexistent quest', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->deleteJson('/api/quests/999');

    $response->assertNotFound();
});