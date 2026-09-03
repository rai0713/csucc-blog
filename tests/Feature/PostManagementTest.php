<?php

use App\Models\Post;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admins can create update and delete posts', function (): void {
    $admin = User::factory()->admin()->create();
    Sanctum::actingAs($admin);

    $createResponse = $this->postJson('/api/posts', [
        'title' => 'The New Publishing Flow',
        'excerpt' => 'A short introduction to the new admin workflow.',
        'body' => 'The new workflow keeps token auth and admin permissions explicit.',
        'published_at' => now()->toISOString(),
    ]);

    $createResponse->assertCreated()
        ->assertJsonPath('data.slug', 'the-new-publishing-flow');

    $this->assertDatabaseHas('posts', [
        'title' => 'The New Publishing Flow',
        'slug' => 'the-new-publishing-flow',
        'user_id' => $admin->id,
    ]);

    $post = Post::query()->where('slug', 'the-new-publishing-flow')->firstOrFail();

    $updateResponse = $this->putJson('/api/posts/'.$post->slug, [
        'title' => 'The Updated Publishing Flow',
        'excerpt' => 'A sharper summary for the editor.',
        'body' => 'The edited version keeps the same post in place.',
        'published_at' => now()->toISOString(),
    ]);

    $updateResponse->assertOk()
        ->assertJsonPath('data.slug', 'the-updated-publishing-flow');

    $this->assertDatabaseHas('posts', [
        'title' => 'The Updated Publishing Flow',
        'slug' => 'the-updated-publishing-flow',
    ]);

    $this->deleteJson('/api/posts/the-updated-publishing-flow')->assertOk();

    $this->assertDatabaseMissing('posts', [
        'slug' => 'the-updated-publishing-flow',
    ]);
});

test('non-admin users cannot create posts', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $this->postJson('/api/posts', [
        'title' => 'Blocked Post',
        'excerpt' => 'This should not be created.',
        'body' => 'Role middleware should stop this request.',
    ])->assertForbidden();
});
