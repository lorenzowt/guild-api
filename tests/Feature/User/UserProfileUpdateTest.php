<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('authenticated user can update selected profile fields', function () {
    $user = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson('/api/users/me', [
            'name' => 'Arthas the Lich King',
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'Arthas the Lich King',
                'email' => 'arthas@example.com',
                'role' => UserRole::User->value,
            ],
            'message' => 'User profile updated successfully.',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Arthas the Lich King',
        'email' => 'arthas@example.com',
    ]);
});

test('authenticated user can update their password', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson('/api/users/me', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertOk()
        ->assertJsonPath('message', 'User profile updated successfully.')
        ->assertJsonMissingPath('data.password');

    expect(Hash::check('new-password', $user->fresh()->password))
        ->toBeTrue();
});

test('authenticated admin can update their own profile', function () {
    $admin = User::factory()->create([
        'name' => 'Guild Master',
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson('/api/users/me', [
            'name' => 'Grand Guild Master',
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $admin->id,
                'name' => 'Grand Guild Master',
                'role' => UserRole::Admin->value,
            ],
        ]);
});

test('authenticated user cannot update profile with invalid data', function (
    array $payload,
    string $field
) {
    $user = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
    ]);

    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson('/api/users/me', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
    ]);
})->with([
    'empty name' => [['name' => ''], 'name'],
    'name is too long' => [['name' => str_repeat('a', 256)], 'name'],
    'name has invalid type' => [['name' => ['Arthas']], 'name'],

    'empty email' => [['email' => ''], 'email'],
    'email has invalid format' => [['email' => 'invalid-email'], 'email'],
    'email is already taken' => [['email' => 'taken@example.com'], 'email'],

    'password is too long' => [[
        'password' => str_repeat('a', 101),
        'password_confirmation' => str_repeat('a', 101),
    ], 'password'],

    'password confirmation does not match' => [[
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ], 'password'],
]);

test('authenticated user cannot update profile without an updatable field', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->patchJson('/api/users/me', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['request']);
});

test('authenticated user cannot change their own role', function () {
    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($user, 'api')
        ->patchJson('/api/users/me', [
            'name' => 'New Name',
            'role' => UserRole::Admin->value,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.role', UserRole::User->value);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'role' => UserRole::User->value,
    ]);
});

test('unauthenticated user cannot update a profile', function () {
    $response = $this->patchJson('/api/users/me', [
        'name' => 'New Name',
    ]);

    $response->assertUnauthorized();
});