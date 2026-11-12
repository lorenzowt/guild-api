<?php

use App\Enums\HeroClass;
use App\Enums\HeroStatus;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can successfully complete a mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'difficulty' => 5,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $warrior = Hero::factory()->create([
        'user_id' => $user->id,
        'hero_class' => HeroClass::Warrior,
        'level' => 3,
        'status' => HeroStatus::OnMission,
    ]);

    $mage = Hero::factory()->create([
        'user_id' => $user->id,
        'hero_class' => HeroClass::Mage,
        'level' => 2,
        'status' => HeroStatus::OnMission,
    ]);

    $mission->heroes()->attach([
        $warrior->id,
        $mage->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/complete");

    $response->assertOk()
        ->assertJsonPath('data.id', $mission->id)
        ->assertJsonPath('data.status', MissionStatus::Success->value)
        ->assertJsonPath('message', 'Mission completed successfully.');

    expect($response->json('data.completed_at'))->not->toBeNull();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Success->value,
    ]);

    expect($mission->fresh()->completed_at)->not->toBeNull();

    $this->assertDatabaseHas('heroes', [
        'id' => $warrior->id,
        'level' => 4,
        'status' => HeroStatus::Available->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $mage->id,
        'level' => 3,
        'status' => HeroStatus::Available->value,
    ]);
});

test('authenticated user completes a mission unsuccessfully when party level equals quest difficulty', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'difficulty' => 3,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $firstWarrior = Hero::factory()->create([
        'user_id' => $user->id,
        'hero_class' => HeroClass::Warrior,
        'level' => 2,
        'status' => HeroStatus::OnMission,
    ]);

    $secondWarrior = Hero::factory()->create([
        'user_id' => $user->id,
        'hero_class' => HeroClass::Warrior,
        'level' => 1,
        'status' => HeroStatus::OnMission,
    ]);

    $mission->heroes()->attach([
        $firstWarrior->id,
        $secondWarrior->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/complete");

    $response->assertOk()
        ->assertJsonPath('data.id', $mission->id)
        ->assertJsonPath('data.status', MissionStatus::Failure->value)
        ->assertJsonPath('message', 'Mission completed successfully.');

    expect($response->json('data.completed_at'))->not->toBeNull();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Failure->value,
    ]);

    expect($mission->fresh()->completed_at)->not->toBeNull();

    $this->assertDatabaseHas('heroes', [
        'id' => $firstWarrior->id,
        'level' => 1,
        'status' => HeroStatus::Injured->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $secondWarrior->id,
        'level' => 1,
        'status' => HeroStatus::Injured->value,
    ]);
});

test('authenticated user cannot complete another users mission', function () {
    $missionOwner = User::factory()->create();

    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'difficulty' => 1,
    ]);

    $mission = $missionOwner->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/complete");

    $response->assertNotFound();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);
});

test('unauthenticated user cannot complete a mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'difficulty' => 1,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $response = $this->postJson("/api/missions/{$mission->id}/complete");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);
});

test('authenticated user cannot complete a mission that is already completed', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'difficulty' => 1,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'hero_class' => HeroClass::Warrior,
        'level' => 4,
        'status' => HeroStatus::OnMission,
    ]);

    $mission->heroes()->attach($hero->id);

    $mission->forceFill([
        'status' => MissionStatus::Success,
        'completed_at' => now(),
    ])->save();

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/complete");

    $response->assertConflict();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Success->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'level' => 4,
        'status' => HeroStatus::OnMission->value,
    ]);
});

test('authenticated user cannot complete a canceled mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'difficulty' => 1,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $mission->forceFill([
        'status' => MissionStatus::Canceled,
    ])->save();

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/complete");

    $response->assertConflict();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Canceled->value,
        'completed_at' => null,
    ]);
});