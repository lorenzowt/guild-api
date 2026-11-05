<?php

use App\Models\Quest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receives active quest information', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests/$quest->id");

    $response->assertStatus(200)    
        ->assertJson([
                'data' => [
                    'title' => $quest->name,
                    'description' => $quest->description,
                    'boss' => $quest->boss,
                    'difficulty' => $quest->difficulty,
                ],
                'message' => 'Quest retrieved successfully.'
            ])
            ->assertJsonStructure([
                'data' => ['title','description','boss','difficulty','is_active','created_at','updated_at'],
                'message',
            ]);
});

test('unauthenticated user cannot receive information from a quest', function () {
    $user = User::factory()->create();

    $quest = Quest::factory()->create();

    $response = $this->getJson("/api/quests/$quest->id");

    $response->assertUnauthorized();
});

test('authenticated user do not receive inactive quests', function() {
    $user = User::factory()->create();

    $quest = Quest::factory()->create([
        'is_active' => false,
    ]);

    $this->assertDatabaseHas('quests', [
        'id' => $quest->id,
        'is_active' => false,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/quests/$quest->id");

    $response->assertNotFound();
});
