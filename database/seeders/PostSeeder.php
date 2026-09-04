<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        if (Post::query()->exists()) {
            return;
        }

        $admin = User::query()->where('role', 'admin')->first();

        if (! $admin) {
            $admin = User::factory()->admin()->create();
        }

        Post::query()->createMany([
            [
                'user_id' => $admin->id,
                'title' => 'Welcome to CSUCC Blog',
                'slug' => 'welcome-to-csucc-blog',
                'excerpt' => 'A simple place for stories, updates, and ideas from the CSUCC community.',
                'body' => 'This is the first sample post on CSUCC Blog. Share useful stories and updates with the community.',
                'published_at' => now()->subDays(2),
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Building Better Ideas Together',
                'slug' => 'building-better-ideas-together',
                'excerpt' => 'Good conversations help turn small ideas into meaningful projects.',
                'body' => 'Collaboration gives every idea room to grow. Use this sample post as a starting point for your own story.',
                'published_at' => now()->subDay(),
            ],
            [
                'user_id' => $admin->id,
                'title' => 'A Fresh Start for the Blog',
                'slug' => 'a-fresh-start-for-the-blog',
                'excerpt' => 'The blog is ready for its first collection of posts and announcements.',
                'body' => 'This sample post shows how published content appears on the blog homepage.',
                'published_at' => now(),
            ],
        ]);
    }
}
