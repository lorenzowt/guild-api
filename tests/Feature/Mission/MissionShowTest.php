<?php

use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can receive information about their mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $heroes = Hero::factory(2)->create([
        'user_id' => $user->id,
    ]);

    $mission->heroes()->attach($heroes->pluck('id')->all());

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/missions/{$mission->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $mission->id,
                'status' => MissionStatus::Assigned->value,
                'quest' => [
                    'id' => $quest->id,
                    'title' => $quest->title,
                ],
            ],
            'message' => 'Mission retrieved successfully.',
        ])
        ->assertJsonCount(2, 'data.heroes')
        ->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'quest' => [
                    'id',
                    'title',
                ],
                'heroes' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'completed_at',
                'created_at',
            ],
            'message',
        ]);
});

test('authenticated user cannot receive information about another users mission', function () {
    $missionOwner = User::factory()->create();

    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $missionOwner->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/missions/{$mission->id}");

    $response->assertNotFound();
});

test('unauthenticated user cannot receive information about a mission', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $user->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $response = $this->getJson("/api/missions/{$mission->id}");

    $response->assertUnauthorized();
});