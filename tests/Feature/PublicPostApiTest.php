<?php

use App\Models\Post;
use App\Models\User;

test('public visitors can list published posts', function (): void {
    $post = Post::factory()->create([
        'title' => 'Public Story',
        'slug' => 'public-story',
        'user_id' => User::factory()->create()->id,
        'published_at' => now(),
    ]);

    $response = $this->getJson('/api/posts');

    $response->assertOk()
        ->assertJsonFragment([
            'slug' => $post->slug,
            'title' => $post->title,
        ]);
});

test('public visitors can read a single post', function (): void {
    Post::factory()->create([
        'title' => 'Public Detail Story',
        'slug' => 'public-detail-story',
        'user_id' => User::factory()->create()->id,
        'published_at' => now(),
    ]);

    $response = $this->getJson('/api/posts/public-detail-story');

    $response->assertOk()
        ->assertJsonPath('data.slug', 'public-detail-story')
        ->assertJsonPath('data.title', 'Public Detail Story');
});
