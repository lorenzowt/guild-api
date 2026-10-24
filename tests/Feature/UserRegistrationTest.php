<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a visitor can register an account (password gets hashed and never returned', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonMissingPath('password')
        ->assertJsonMissingPath('password_hash');

    $this->assertDatabaseHas('users', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'role' => 'user',
    ]);

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect(Hash::check('password123', $user->password))->toBeTrue();
});

test('a visitor cannot register as an admin', function() {
    $response = $this->postJson('/api/register', [
        'name' => 'Test Admin',
        'email' => 'admin@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'admin@example.com',
        'role' => 'user',
    ]);
});

test('a visitor cannot register with an email that is already taken', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'Another User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('a visitor cannot register with mismatching password confirmation', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Example Name',
        'email' => 'example@email.com',
        'password' => 'password123',
        'password_confirmation' => 'password124',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

test('a visitor cannot register with invalid name, malformed email and invalid password', function () {
    $response = $this->postJson('/api/register', [
        'name' => '',
        'email' => 'email',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});