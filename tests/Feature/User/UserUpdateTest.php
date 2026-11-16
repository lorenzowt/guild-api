<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin can update selected fields for another user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/users/{$user->id}", [
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
            'message' => 'User updated successfully.',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Arthas the Lich King',
        'email' => 'arthas@example.com',
        'role' => UserRole::User->value,
    ]);
});

test('admin can reset another users password', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/users/{$user->id}", [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertOk()
        ->assertJsonPath('message', 'User updated successfully.')
        ->assertJsonMissingPath('data.password');

    expect(Hash::check('new-password', $user->fresh()->password))
        ->toBeTrue();
});

test('admin cannot update a user with invalid data', function (
    array $payload,
    string $field
) {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
    ]);

    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/users/{$user->id}", $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
        'role' => UserRole::User->value,
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

test('admin cannot update a user without providing an updatable field', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/users/{$user->id}", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['request']);
});

test('admin cannot change a users role through the update endpoint', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $user = User::factory()->create([
        'role' => UserRole::User,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'role' => UserRole::Admin->value,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.role', UserRole::User->value);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'role' => UserRole::User->value,
    ]);
});

test('regular user cannot update another user', function () {
    $user = User::factory()->create();

    $otherUser = User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->patchJson("/api/users/{$otherUser->id}", [
            'name' => 'New Name',
        ]);

    $response->assertForbidden();
});

test('unauthenticated user cannot update a user', function () {
    $user = User::factory()->create();

    $response = $this->patchJson("/api/users/{$user->id}", [
        'name' => 'New Name',
    ]);

    $response->assertUnauthorized();
});

test('admin receives not found when updating a nonexistent user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->patchJson('/api/users/999', [
            'name' => 'New Name',
        ]);

    $response->assertNotFound();
});