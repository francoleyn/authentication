<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['user', 'categories'])
            ->published()
            ->latest('published_at');

        // Search
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->inCategory($request->category_id);
        }

        $posts = $query->paginate(15);

        return response()->json([
            'posts' => $posts,
        ]);
    }

    public function show(Post $post)
    {
        $post->load(['user', 'categories', 'comments.user']);

        return response()->json([
            'post' => $post,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $post = $request->user()->posts()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'is_published' => $request->is_published ?? false,
            'published_at' => $request->is_published ? now() : null,
        ]);

        if ($request->has('category_ids')) {
            $post->categories()->sync($request->category_ids);
        }

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load('categories'),
        ], 201);
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'nullable|string|max:500',
            'is_published' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $post->update($request->only(['title', 'content', 'excerpt', 'is_published']));

        if ($request->is_published && !$post->published_at) {
            $post->update(['published_at' => now()]);
        }

        if ($request->has('category_ids')) {
            $post->categories()->sync($request->category_ids);
        }

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->load('categories'),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id && !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    public function myPosts(Request $request)
    {
        $posts = $request->user()->posts()
            ->with('categories')
            ->latest()
            ->paginate(15);

        return response()->json([
            'posts' => $posts,
        ]);
    }
}
