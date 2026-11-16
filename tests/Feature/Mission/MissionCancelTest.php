<?php

use App\Enums\HeroClass;
use App\Enums\HeroStatus;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\UserRole;

uses(RefreshDatabase::class);

test('authenticated user can cancel an assigned mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'hero_class' => HeroClass::Warrior,
        'level' => 3,
        'status' => HeroStatus::OnMission,
    ]);

    $mission->heroes()->attach($hero->id);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/cancel");

    $response->assertOk()
        ->assertJsonPath('data.id', $mission->id)
        ->assertJsonPath('data.status', MissionStatus::Canceled->value)
        ->assertJsonPath('data.completed_at', null)
        ->assertJsonPath('message', 'Mission canceled successfully.');

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Canceled->value,
        'completed_at' => null,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'level' => 3,
        'status' => HeroStatus::Available->value,
    ]);
});

test('authenticated user cannot cancel another users mission', function () {
    $missionOwner = User::factory()->create();

    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $missionOwner->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/cancel");

    $response->assertNotFound();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);
});

test('unauthenticated user cannot cancel a mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $response = $this->postJson("/api/missions/{$mission->id}/cancel");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);
});

test('authenticated user cannot cancel a mission that is not assigned', function (
    MissionStatus $status
) {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $mission->forceFill([
        'status' => $status,
    ])->save();

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/cancel");

    $response->assertConflict()
        ->assertJsonPath('message', 'Only assigned missions can be canceled.');

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => $status->value,
    ]);
})->with([
    'successful mission' => [MissionStatus::Success],
    'failed mission' => [MissionStatus::Failure],
    'canceled mission' => [MissionStatus::Canceled],
]);

test('admin can cancel another users assigned mission', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $missionOwner = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $missionOwner->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $missionOwner->id,
        'hero_class' => HeroClass::Warrior,
        'level' => 3,
        'status' => HeroStatus::OnMission,
    ]);

    $mission->heroes()->attach($hero->id);

    $response = $this->actingAs($admin, 'api')
        ->postJson("/api/missions/{$mission->id}/cancel");

    $response->assertOk()
        ->assertJsonPath('data.id', $mission->id)
        ->assertJsonPath('data.status', MissionStatus::Canceled->value)
        ->assertJsonPath('data.completed_at', null)
        ->assertJsonPath('message', 'Mission canceled successfully.');

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'user_id' => $missionOwner->id,
        'status' => MissionStatus::Canceled->value,
        'completed_at' => null,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'level' => 3,
        'status' => HeroStatus::Available->value,
    ]);
});

test('admin receives not found when canceling a nonexistent mission', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->postJson('/api/missions/999/cancel');

    $response->assertNotFound();
});