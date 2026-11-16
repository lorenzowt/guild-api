<?php

use App\Enums\HeroStatus;
use App\Enums\UserRole;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can delete a user and their related heroes and missions', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create();

    $questCreatedByUser = Quest::factory()->create([
        'created_by' => $user->id,
    ]);

    $missionQuest = Quest::factory()->create([
        'created_by' => $admin->id,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $missionQuest->id,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::OnMission,
    ]);

    $mission->heroes()->attach($hero->id);

    $response = $this->actingAs($admin, 'api')
        ->deleteJson("/api/users/{$user->id}");

    $response->assertOk()
        ->assertJson([
            'message' => 'User deleted successfully.',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);

    $this->assertDatabaseMissing('missions', [
        'id' => $mission->id,
    ]);

    $this->assertDatabaseMissing('heroes', [
        'id' => $hero->id,
    ]);

    $this->assertDatabaseMissing('hero_mission', [
        'mission_id' => $mission->id,
        'hero_id' => $hero->id,
    ]);

    $this->assertDatabaseHas('quests', [
        'id' => $questCreatedByUser->id,
        'created_by' => null,
    ]);
});

test('regular user cannot delete a user account', function () {
    $user = User::factory()->create();

    $targetUser = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->deleteJson("/api/users/{$targetUser->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
    ]);
});

test('unauthenticated user cannot delete a user account', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});

test('admin receives not found when deleting a nonexistent user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->deleteJson('/api/users/999');

    $response->assertNotFound();
});