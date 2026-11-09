<?php

use App\Enums\HeroStatus;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Mission;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create a mission with available heroes', function (int $heroCount) {
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

    $response->assertCreated()
        ->assertJson([
            'data' => [
                'status' => MissionStatus::Assigned->value,
                'quest' => [
                    'id' => $quest->id,
                    'title' => $quest->title,
                ],
            ],
        ])
        ->assertJsonCount($heroCount, 'data.heroes')
        ->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'quest' => ['id', 'title'],
                'heroes' => [
                    '*' => ['id', 'name'],
                ],
                'completed_at',
                'created_at',
            ],
        ]);

    $mission = Mission::firstOrFail();

    $this->assertDatabaseHas('missions', [
        'id' => $mission->id,
        'user_id' => $user->id,
        'quest_id' => $quest->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);

    $this->assertDatabaseCount('missions', 1);
    $this->assertDatabaseCount('hero_mission', $heroCount);

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
    'one hero' => [1],
    'three heroes' => [3],
    'maximum four heroes' => [4],
]);


test('authenticated user cannot create mission with invalid quest', function (string $scenario) {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => $scenario !== 'inactive',
    ]);

    $heroes = Hero::factory(2)->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Available,
    ]);

    $payload = [
        'quest_id' => $quest->id,
        'hero_ids' => $heroes->pluck('id')->all(),
    ];

    if ($scenario === 'missing') {
        unset($payload['quest_id']);
    }

    if ($scenario === 'nonexistent') {
        $payload['quest_id'] = 99999;
    }

    if ($scenario === 'invalid type') {
        $payload['quest_id'] = 'invalid';
    }

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quest_id']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

    foreach ($heroes as $hero) {
        $this->assertDatabaseHas('heroes', [
            'id' => $hero->id,
            'status' => HeroStatus::Available->value,
        ]);
    }

})->with([
    'missing quest' => ['missing'],
    'nonexistent quest' => ['nonexistent'],
    'inactive quest' => ['inactive'],
    'invalid data type' => ['invalid type'],
]);


test('authenticated user cannot create mission with invalid hero_ids', function (array $payload) {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', array_merge([
            'quest_id' => $quest->id,
        ], $payload));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_ids']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

})->with([
    'missing heroes' => [[]],
    'empty heroes array' => [['hero_ids' => []]],
    'too many heroes' => [['hero_ids' => [1, 2, 3, 4, 5]]],
    'invalid data type' => [['hero_ids' => 'invalid']],
]);


test('authenticated user cannot create mission with invalid individual hero', function (array $payload) {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', array_merge([
            'quest_id' => $quest->id,
        ], $payload));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_ids.0']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

})->with([
    'nonexistent hero' => [['hero_ids' => [99999]]],
    'invalid hero type' => [['hero_ids' => ['invalid']]],
    'null hero id' => [['hero_ids' => [null]]],
]);


test('authenticated user cannot create mission with duplicate heroes', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Available,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', [
            'quest_id' => $quest->id,
            'hero_ids' => [$hero->id, $hero->id],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_ids.0']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Available->value,
    ]);
});


test('authenticated user cannot create mission with another users hero', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $otherUser->id,
        'status' => HeroStatus::Available,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', [
            'quest_id' => $quest->id,
            'hero_ids' => [$hero->id],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_ids.0']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'user_id' => $otherUser->id,
        'status' => HeroStatus::Available->value,
    ]);
});


test('authenticated user cannot create mission with unavailable hero', function (HeroStatus $status) {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => $status,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', [
            'quest_id' => $quest->id,
            'hero_ids' => [$hero->id],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_ids.0']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => $status->value,
    ]);

})->with([
    'hero on mission' => [HeroStatus::OnMission],
    'injured hero' => [HeroStatus::Injured],
    'deactivated hero' => [HeroStatus::Deactivated],
]);


test('failed mission creation does not change available hero status', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $availableHero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Available,
    ]);

    $injuredHero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Injured,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', [
            'quest_id' => $quest->id,
            'hero_ids' => [
                $availableHero->id,
                $injuredHero->id,
            ],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_ids.1']);

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

    $this->assertDatabaseHas('heroes', [
        'id' => $availableHero->id,
        'status' => HeroStatus::Available->value,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $injuredHero->id,
        'status' => HeroStatus::Injured->value,
    ]);
});


test('authenticated user cannot override mission status or owner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Available,
    ]);

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/missions', [
            'quest_id' => $quest->id,
            'hero_ids' => [$hero->id],
            'user_id' => $otherUser->id,
            'status' => MissionStatus::Success->value,
            'completed_at' => '2026-01-01 12:00:00',
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('missions', [
        'user_id' => $user->id,
        'quest_id' => $quest->id,
        'status' => MissionStatus::Assigned->value,
        'completed_at' => null,
    ]);

    $this->assertDatabaseMissing('missions', [
        'user_id' => $otherUser->id,
    ]);
});


test('unauthenticated user cannot create a mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => true,
    ]);

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Available,
    ]);

    $response = $this->postJson('/api/missions', [
        'quest_id' => $quest->id,
        'hero_ids' => [$hero->id],
    ]);

    $response->assertUnauthorized();

    $this->assertDatabaseCount('missions', 0);
    $this->assertDatabaseCount('hero_mission', 0);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Available->value,
    ]);
});
