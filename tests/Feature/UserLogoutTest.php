<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);


test('a user can log out and their token is revoked', function () {
    Client::factory()
    ->asPersonalAccessTokenClient()
    ->create([
        'name' => 'Guild API',
        'provider' => 'users',
    ]);

    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $loginResponse = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $loginResponse->assertOk();

    $token = $loginResponse->json('access_token');

    $logoutResponse = $this->withToken($token)
        ->postJson('/api/logout');

    $logoutResponse->assertOk();

    Auth::forgetGuards();

    $this->assertDatabaseHas('oauth_access_tokens', [
    'user_id' => $user->id,
    'revoked' => true,
]);

    $protectedResponse = $this->withToken($token)->getJson('/api/user');

    $protectedResponse->assertUnauthorized();
});

test('an unauthenticated user cannot log out', function () {
    $response = $this->postJson('/api/logout');

    $response->assertUnauthorized();
});