<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a visitor can register an account', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('users', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'role' => 'user',
    ]);

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect(Hash::check('password123', $user->password))->toBeTrue();
});