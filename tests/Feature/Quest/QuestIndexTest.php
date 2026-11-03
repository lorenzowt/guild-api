<?php

use App\Models\User;
use App\Models\Quest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receives a paginated list of the quests', function($perPage) {
    $user = User::factory()->create();

    Quest::factory(10)->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests?per_page={$perPage}");
    
    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Quests retrieved successfully.',
        ])
        ->assertJsonCount(
            $perPage === null || $perPage === '' ? 10 : $perPage,
            'data'
        )
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'title',
                    'description',
                    'boss',
                    'difficulty',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'message',
            'links',
            'meta',
        ]);
})->with([
    'one quest per page' => 1,
    'five quests per page' => 5,
    'null uses the default' => null,
    'empty uses the default' => '',
]);