<?php

use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receives a paginated list of the missions they own', function ($perPage) {
    $user = User::factory()->create();

    $missions = collect(range(1, 10))
        ->map(function () use ($user) {
            $quest = Quest::factory()->create();

            return $user->missions()->create([
                'quest_id' => $quest->id,
            ]);
        });

    $otherUser = User::factory()->create();

    collect(range(1, 3))
        ->each(function () use ($otherUser) {
            $quest = Quest::factory()->create();

            $otherUser->missions()->create([
                'quest_id' => $quest->id,
            ]);
        });

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/missions?per_page={$perPage}");

    $expectedCount = $perPage === null || $perPage === '' ? 10 : $perPage;

    $response->assertOk()
        ->assertJsonCount($expectedCount, 'data')
        ->assertJsonPath('message', 'Missions retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'status',
                    'quest' => [
                        'id',
                        'title',
                    ],
                    'heroes',
                    'completed_at',
                    'created_at',
                ],
            ],
            'message',
            'links',
            'meta',
        ]);

    $returnedMissionIds = collect($response->json('data'))
        ->pluck('id');

    expect(
        $returnedMissionIds->diff($missions->pluck('id'))->all()
    )->toBe([]);
})->with([
    'one mission per page' => 1,
    'five missions per page' => 5,
    'null uses the default' => null,
    'empty uses the default' => '',
]);

test('authenticated user receives mission quest and assigned hero information', function () {
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
        ->getJson('/api/missions');

    $response->assertOk()
        ->assertJson([
            'data' => [
                [
                    'id' => $mission->id,
                    'quest' => [
                        'id' => $quest->id,
                        'title' => $quest->title,
                    ],
                ],
            ],
            'message' => 'Missions retrieved successfully.',
        ])
        ->assertJsonFragment([
            'id' => $heroes[0]->id,
            'name' => $heroes[0]->name,
        ])
        ->assertJsonFragment([
            'id' => $heroes[1]->id,
            'name' => $heroes[1]->name,
        ]);
});

test('authenticated user without missions retrieves an empty list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson('/api/missions');

    $response->assertOk()
        ->assertJsonCount(0, 'data')
        ->assertJsonPath('meta.total', 0)
        ->assertJsonPath('message', 'Missions retrieved successfully.');
});

test('invalid per_page values are rejected', function ($perPage) {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/missions?per_page={$perPage}");

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('per_page');
})->with([
    'above maximum' => 101,
    'zero' => 0,
    'below minimum' => -1,
    'non-numeric value' => 'abc',
]);

test('unauthenticated user cannot retrieve missions', function () {
    $response = $this->getJson('/api/missions');

    $response->assertUnauthorized();
});