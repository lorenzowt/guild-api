<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can retrieve a paginated list of all registered users', function () {
    $admin = User::factory()->create([
        'name' => 'Guild Master',
        'email' => 'admin@example.com',
        'role' => UserRole::Admin,
    ]);

    $firstUser = User::factory()->create([
        'name' => 'Arthas Menethil',
        'email' => 'arthas@example.com',
    ]);

    $secondUser = User::factory()->create([
        'name' => 'Jaina Proudmoore',
        'email' => 'jaina@example.com',
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/users');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('message', 'Users retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at',
                    'updated_at',
                ],
            ],
            'message',
            'links',
            'meta',
        ])
        ->assertJsonFragment([
            'id' => $admin->id,
            'name' => 'Guild Master',
            'email' => 'admin@example.com',
            'role' => UserRole::Admin->value,
        ])
        ->assertJsonFragment([
            'id' => $firstUser->id,
            'name' => 'Arthas Menethil',
            'email' => 'arthas@example.com',
            'role' => UserRole::User->value,
        ])
        ->assertJsonFragment([
            'id' => $secondUser->id,
            'name' => 'Jaina Proudmoore',
            'email' => 'jaina@example.com',
            'role' => UserRole::User->value,
        ]);
});

test('admin can choose the number of users per page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    User::factory(4)->create();

    $response = $this->actingAs($admin, 'api')
        ->getJson('/api/users?per_page=2');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.total', 5);
});

test('regular user cannot retrieve all registered users', function () {
    $user = User::factory()->create();

    User::factory()->create();

    $response = $this->actingAs($user, 'api')
        ->getJson('/api/users');

    $response->assertForbidden();
});

test('unauthenticated user cannot retrieve registered users', function () {
    $response = $this->getJson('/api/users');

    $response->assertUnauthorized();
});

test('admin cannot retrieve users with an invalid per page value', function ($perPage) {
    $admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    $response = $this->actingAs($admin, 'api')
        ->getJson("/api/users?per_page={$perPage}");

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page']);
})->with([
    'above maximum' => 101,
    'zero' => 0,
    'below minimum' => -1,
    'non numeric value' => 'invalid',
]);