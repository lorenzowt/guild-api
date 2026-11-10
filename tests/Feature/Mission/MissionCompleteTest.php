<?php

use App\Enums\HeroClass;
use App\Enums\HeroStatus;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAssignedMissionForCompletionTest(
    User $user,
    int $difficulty,
    array $heroAttributes
): array {
    $quest = Quest::factory()->create([
        'difficulty' => $difficulty,
    ]);

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $heroes = collect($heroAttributes)
        ->map(function (array $attributes) use ($user) {
            return Hero::factory()->create(array_merge([
                'user_id' => $user->id,
                'hero_class' => HeroClass::Warrior,
                'level' => 1,
                'status' => HeroStatus::OnMission,
            ], $attributes));
        });

    $mission->heroes()->attach($heroes->pluck('id')->all());

    return [$mission, $heroes];
}

test('authenticated user can successfully complete a mission', function () {
    $user = User::factory()->create();

    [$mission, $heroes] = createAssignedMissionForCompletionTest(
        user: $user,
        difficulty: 5,
        heroAttributes: [
            [
                'hero_class' => HeroClass::Warrior,
                'level' => 3,
            ],
            [
                'hero_class' => HeroClass::Mage,
                'level' => 2,
            ],
        ],
    );

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
        'id' => $heroes[0]->id,
        'level' => 4,
        'status' => HeroStatus::Available->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $heroes[1]->id,
        'level' => 3,
        'status' => HeroStatus::Available->value,
    ]);
});

test('authenticated user completes a mission unsuccessfully when party level equals quest difficulty', function () {
    $user = User::factory()->create();

    [$mission, $heroes] = createAssignedMissionForCompletionTest(
        user: $user,
        difficulty: 3,
        heroAttributes: [
            [
                'hero_class' => HeroClass::Warrior,
                'level' => 2,
            ],
            [
                'hero_class' => HeroClass::Warrior,
                'level' => 1,
            ],
        ],
    );

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
        'id' => $heroes[0]->id,
        'level' => 1,
        'status' => HeroStatus::Injured->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $heroes[1]->id,
        'level' => 1,
        'status' => HeroStatus::Injured->value,
    ]);
});

test('authenticated user cannot complete another users mission', function () {
    $missionOwner = User::factory()->create();

    $user = User::factory()->create();

    [$mission] = createAssignedMissionForCompletionTest(
        user: $missionOwner,
        difficulty: 1,
        heroAttributes: [
            ['level' => 2],
        ],
    );

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

    [$mission] = createAssignedMissionForCompletionTest(
        user: $user,
        difficulty: 1,
        heroAttributes: [
            ['level' => 2],
        ],
    );

    $response = $this->postJson("/api/missions/{$mission->id}/complete");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);
});

test('authenticated user cannot complete a mission that is not assigned', function (
    MissionStatus $status
) {
    $user = User::factory()->create();

    [$mission, $heroes] = createAssignedMissionForCompletionTest(
        user: $user,
        difficulty: 1,
        heroAttributes: [
            ['level' => 4],
        ],
    );

    $mission->forceFill([
        'status' => $status,
        'completed_at' => now(),
    ])->save();

    $response = $this->actingAs($user, 'api')
        ->postJson("/api/missions/{$mission->id}/complete");

    $response->assertConflict();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'status' => $status->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $heroes[0]->id,
        'level' => 4,
        'status' => HeroStatus::OnMission->value,
    ]);
})->with([
    'successful mission' => [MissionStatus::Success],
    'failed mission' => [MissionStatus::Failure],
    'canceled mission' => [MissionStatus::Canceled],
]);