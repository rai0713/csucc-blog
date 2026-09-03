<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PostResource::collection(
            Post::query()
                ->with('author')
                ->whereNotNull('published_at')
                ->latest('published_at')
                ->paginate(6)
        );
    }

    public function store(Request $request): PostResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);

        $post = $request->user()->posts()->create([
            'title' => $validated['title'],
            'slug' => $this->generateUniqueSlug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        return new PostResource($post->load('author'));
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post->load('author'));
    }

    public function update(Request $request, Post $post): PostResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);

        $post->fill([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        if ($post->isDirty('title')) {
            $post->slug = $this->generateUniqueSlug($validated['title'], $post);
        }

        $post->save();

        return new PostResource($post->fresh()->load('author'));
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted.',
        ]);
    }

    private function generateUniqueSlug(string $title, ?Post $ignorePost = null): string
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;
        $suffix = 1;

        while (Post::query()
            ->when($ignorePost, fn ($query) => $query->whereKeyNot($ignorePost->getKey()))
            ->where('slug', $slug)
            ->exists()) {
            $suffix++;
            $slug = $baseSlug.'-'.$suffix;
        }

        return $slug;
    }
}
