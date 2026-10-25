<?php

use App\Models\User;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Hash;

test('a user can log in with valid credentials', function () {
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
