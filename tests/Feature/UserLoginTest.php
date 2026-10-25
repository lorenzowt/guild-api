<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;

uses(RefreshDatabase::class);


test('a user can log in with valid credentials', function () {
    Client::factory()
    ->asPersonalAccessTokenClient()
    ->create([
        'name' => 'Guild API',
        'provider' => 'users',
    ]);

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'access_token',
            'token_type',
        ])
        ->assertJsonPath('token_type', 'Bearer');
});

test('a user cannot log in with incorrect password', function () {

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'message' => 'Invalid credentials'
        ]);

});

test('a user cannot log in with incorrect email', function () {

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'wrogn@email.com',
        'password' => 'password123',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'message' => 'Invalid credentials'
        ]);

});