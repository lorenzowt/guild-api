<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can retrieve their own profile', function () {
    $user = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
        'role' => UserRole::User,
    ]);

    $otherUser = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson('/api/users/me');

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'Arthas Menethil',
                'email' => 'arthas@example.com',
                'role' => UserRole::User->value,
            ],
            'message' => 'User profile retrieved successfully.',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ],
            'message',
        ])
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token');

    expect($response->json('data.id'))->not->toBe($otherUser->id);
});

test('authenticated admin can retrieve their own profile', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/users/me');

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $admin->id,
                'role' => UserRole::Admin->value,
            ],
            'message' => 'User profile retrieved successfully.',
        ]);
});

test('unauthenticated user cannot retrieve a profile', function () {
    $response = $this->getJson('/api/users/me');

    $response->assertUnauthorized();
});