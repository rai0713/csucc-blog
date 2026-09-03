<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        if (! $admin) {
            $admin = User::factory()->admin()->create();
        }

        Post::factory()->count(3)->create([
            'user_id' => $admin->id,
        ]);
    }
}
