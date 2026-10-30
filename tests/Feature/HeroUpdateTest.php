<?php

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can update fillable a field of a hero they own', function ($updateData) {
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

test('authenticated user can update fillable a field of a hero they do not own', function ($updateData) {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $anotherUser->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/heroes/$hero->id", $updateData);

    $response->assertNotFound();

})->with([
    'update name' => [['name' => 'Example']],
    'update class' => [['hero_class' => 'warrior']],
]);

test('authenticated user cannot update unfillable fields', function ($updateData) {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/heroes/$hero->id", $updateData);

    $response->assertUnprocessable();

})->with([
    'update level' => [['level' => 5 ]],
    'update status' => [['status' => 'injured']],
    'update user_id' => [['user_id' => 2 ]],
]);

test('authenticated user cannot update with invalid inputs', function ($updateData) {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/heroes/$hero->id", $updateData);

    $response->assertUnprocessable();

})->with([
    'empty name' => [['name' => '']],
    'null name' => [['name' => null]],
    'non string name' => [['name' => 123 ]],
    'invalid name (too long)' => [['name' => str_repeat('a', 46)]],
    'empty class' => [['hero_class' => '']],
    'null class' => [['hero_class' => null]],
    'non string class' => [['hero_class' => 123 ]],
]);

test('unauthenticated user cannot update a hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->patchJson("/api/heroes/$hero->id", [
        'name' => 'Example Name',
    ]);

    $response->assertUnauthorized();

});




