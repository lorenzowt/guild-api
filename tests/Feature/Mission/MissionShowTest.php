<?php

use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\UserRole;

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
                        'hero_class',
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

test('admin can receive information about another users mission', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $owner = User::factory()->create();

    $quest = Quest::factory()->create();

    $mission = $owner->missions()->create([
        'quest_id' => $quest->id,
    ]);

    $heroes = Hero::factory(2)->create([
        'user_id' => $owner->id,
    ]);

    $mission->heroes()->attach($heroes->pluck('id')->all());

    $response = $this->actingAs($admin, 'api')
        ->getJson("/api/missions/{$mission->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $mission->id,
                'quest' => [
                    'id' => $quest->id,
                    'title' => $quest->title,
                ],
            ],
            'message' => 'Mission retrieved successfully.',
        ])
        ->assertJsonFragment([
            'id' => $heroes[0]->id,
            'name' => $heroes[0]->name,
            'hero_class' => $heroes[0]->hero_class->value,
        ])
        ->assertJsonFragment([
            'id' => $heroes[1]->id,
            'name' => $heroes[1]->name,
            'hero_class' => $heroes[1]->hero_class->value,
        ]);
});
