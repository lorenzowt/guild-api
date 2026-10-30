<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can update fillable a field of their hero', function ($updateData) {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/heroes/$hero->id", $updateData);

    $response->assertStatus(200)    
        ->assertJson([
                'data' => $updateData,
                'message' => 'Hero updated successfully.'
            ])
            ->assertJsonStructure([
                'data' => ['id','name','hero_class','level','status','created_at','updated_at'],
                'message',
            ]);

    $this->assertDatabaseHas('heroes', [
        ...$updateData,
        'id' => $hero->id,
    ]);

})->with([
    'update name' => [['name' => 'Example']],
    'update class' => [['hero_class' => 'warrior']],
]);
