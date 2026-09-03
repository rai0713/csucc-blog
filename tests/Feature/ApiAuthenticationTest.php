<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('guests can register for api access', function (): void {
    $response = $this->postJson('/api/register', [
        'name' => 'Reader One',
        'email' => 'reader-one@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJsonPath('user.role', 'user');

    $this->assertDatabaseHas('users', [
        'email' => 'reader-one@example.test',
        'role' => 'user',
    ]);
});

test('registered users can log in and receive a token', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ])
        ->assertJsonPath('user.email', $user->email);
});

test('authenticated users can log out and revoke their token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('blog-app')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout')
        ->assertOk();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});
