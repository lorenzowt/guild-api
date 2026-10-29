<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can edit a field of their hero', function ($updateInfo) {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/heroes/$hero->id", $updateInfo);

    $response->assertStatus(200)    
        ->assertJson([
                'data' => $updateInfo,
                'message' => 'Hero retrieved successfully.'
            ])
            ->assertJsonStructure([
                'data' => ['id','name','hero_class','level','status','created_at','updated_at'],
                'message',
            ]);
})->with([
    ['name' => 'Example'],
    ['hero_class' => 'warrior'],
]);