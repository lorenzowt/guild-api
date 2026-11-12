<?php
namespace Tests\Feature\Hero;

use App\Models\Hero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Enums\HeroStatus;
use App\Enums\UserRole;

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
                    'hero_class' => $hero->hero_class->value,
                    'level' => $hero->level,
                    'status' => $hero->status->value,
                ],
                'message' => 'Hero retrieved successfully.'
            ])
            ->assertJsonStructure([
                'data' => ['id','name','hero_class','level','status','created_at','updated_at'],
                'message',
            ]);
});

test('authenticated user cannot receive information from a hero they do not own', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($anotherUser, 'api')
        ->getJson("/api/heroes/$hero->id");

    $response->assertNotFound();
});

test('unauthenticated user cannot receive information from hero', function () {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/heroes/$hero->id");

    $response->assertUnauthorized();
});

test('authenticated user do not receive deactivated hero', function() {
    $user = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $user->id,
        'status' => HeroStatus::Deactivated,
    ]);

    $this->assertDatabaseHas('heroes', [
        'id' => $hero->id,
        'status' => HeroStatus::Deactivated->value,
    ]);

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/heroes/$hero->id");

    $response->assertNotFound();
});

test('admin can receive information about any hero including deactivated heroes', function (
    HeroStatus $status
) {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $heroOwner = User::factory()->create();

    $hero = Hero::factory()->create([
        'user_id' => $heroOwner->id,
        'status' => $status,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson("/api/heroes/{$hero->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $hero->id,
                'name' => $hero->name,
                'hero_class' => $hero->hero_class->value,
                'level' => $hero->level,
                'status' => $status->value,
                'user_id' => $heroOwner->id,
            ],
            'message' => 'Hero retrieved successfully.',
        ]);
})->with([
    'active hero' => [HeroStatus::Available],
    'deactivated hero' => [HeroStatus::Deactivated],
]);