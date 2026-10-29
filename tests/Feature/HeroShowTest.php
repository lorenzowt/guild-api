<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receive information on one of their heroes', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/heroes/$hero->id");

    $response->assertStatus(200)    
        ->assertJson([
                'data' => [
                    'name' => $hero->name,
                    'hero_class' => $hero->hero_class,
                    'level' => $hero->level,
                    'status' => $hero->status,
                ],
                'message' => 'Hero retrieved successfully.'
            ])
            ->assertJsonStructure([
                'data' => ['id','name','hero_class','level','status','created_at','updated_at'],
                'message',
            ]);
]);