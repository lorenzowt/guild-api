<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can retrieve a specific user profile', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson("/api/users/{$user->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'Arthas Menethil',
                'email' => 'arthas@example.com',
                'role' => UserRole::User->value,
            ],
            'message' => 'User retrieved successfully.',
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
});

test('regular user cannot retrieve another users profile', function () {
    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson("/api/users/{$otherUser->id}");

    $response->assertForbidden();
});

test('unauthenticated user cannot retrieve a user profile', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertUnauthorized();
});

test('admin receives not found when retrieving a nonexistent user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/users/999');

    $response->assertNotFound();
});