<?php
namespace Tests\Feature;

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create a new hero', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/heroes', [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',
            
        ]);

    $response->assertCreated();

    $response->assertJson([
        'data' => [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',
            'level' => 1,
            'status' => 'available',
        ],
        'message' => 'Hero created successfully.'
    ]);

    $this->assertDatabaseHas('heroes', [
        'name' => 'Hero Example',
        'hero_class' => 'cleric',
        'user_id' => $user->id
    ]);

});

test('authenticated user cannot create hero with invalid name', function(array $payload) 
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/heroes', array_merge([
            'hero_class' => 'cleric',
            ], $payload));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);

    $this->assertDatabaseCount('heroes', 0);

})->with([
    'missing name' => [[]],
    'empty name' => [['name' => '']],
    'invalid name (too long)' => [['name' => str_repeat('a', 46)]],
    'invalid data type' => [['name' => 123.4]]
]);

test('authenticated user cannot create hero with invalid hero_class', function(array $payload) 
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/heroes', array_merge([
            'name' => 'Example Name',
            ], $payload));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['hero_class']);

    $this->assertDatabaseCount('heroes', 0);

})->with([
    'missing hero_class' => [[]],
    'empty hero_class' => [['hero_class' => '']],
    'invalid hero_class' => [['hero_class' => 'baker']],
    'invalid data type' => [['hero_class' => ['mage','warrior']]]
]);

test('an authenticated user cannot overwrite level or status', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->postJson('/api/heroes', [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',
            'level' => 9,
            'status' => 'on_mission',
        ]);

    $response->assertCreated();

    $response->assertJson([
        'data' => [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',
            'level' => 1,
            'status' => 'available',
        ],
        'message' => 'Hero created successfully.'
    ]);

    $this->assertDatabaseHas('heroes', [
        'name' => 'Hero Example',
        'hero_class' => 'cleric',
        'level' => 1,
        'status' => 'available',
    ]);

});

test('unauthenticated user cannot create a new hero', function () {

    $response = $this->postJson('/api/heroes', [
            'name' => 'Hero Example',
            'hero_class' => 'cleric',  
        ]);

    $response->assertUnauthorized();

    $this->assertDatabaseCount('heroes', 0);

});
