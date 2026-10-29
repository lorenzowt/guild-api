<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;

uses(RefreshDatabase::class);


test('a user can log in with valid credentials, password or hash are not returned', function () {
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
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonMissingPath('password')
        ->assertJsonMissingPath('password_hash');
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
            'message' => 'Invalid credentials.'
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
            'message' => 'Invalid credentials.'
        ]);

});

test('a user cannot log in with missing email', function () {

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'password' => 'password123',
    ]);

    $response->assertUnprocessable();

});

test('a user cannot log in with missing password', function () {

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
    ]);

    $response->assertUnprocessable();

});

test('a user cannot log in with malformed email and it request is not processed', function () {

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'malformed-email',
        'password' => 'password123',
    ]);

    $response->assertUnprocessable();

});


test('a user can access a protected endpoint with their token', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    Client::factory()
        ->asPersonalAccessTokenClient()
        ->create([
            'name' => 'Guild API',
            'provider' => 'users',
        ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $loginResponse->assertOk();

    $token = $loginResponse->json('access_token');

    $response = $this->withToken($token)->getJson('/api/user');

    $response->assertOk()
        ->assertJsonPath('id', $user->id)
        ->assertJsonPath('email', $user->email);
});


