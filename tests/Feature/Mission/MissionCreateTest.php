<?php

use App\Models\User;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\Mission;
use App\Enums\HeroStatus;
use App\Enums\MissionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create a mission with available heroes', function ($heroCount) {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $heroes = Hero::factory($heroCount)->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Available,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', [
            'quest_id' => $quest->id,
            'hero_ids' => $heroes->pluck('id')->all(),
        ]);

    $response->assertStatus(201);

    $mission = Mission::firstOrFail();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'user_id' => $user->id,
        'quest_id' => $quest->id,
        'status' => MissionStatus::Assigned->value,
    ]);

    foreach ($heroes as $hero) {
        $this->assertDatabaseHas('hero_mission', [
            'mission_id' => $mission->id,
            'hero_id' => $hero->id,
        ]);

        $this->assertDatabaseHas('heroes', [
            'id' => $hero->id,
            'status' => HeroStatus::OnMission->value,
        ]);
    }
})->with([
    '1' => 1,
    '2' => 2,
    '3' => 3,
    '4' => 4,
]);
