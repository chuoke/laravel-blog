<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Actions\PostList;
use Chuoke\Blog\Actions\PostGet;
use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Actions\PostUpdate;
use Chuoke\Blog\Actions\PostDelete;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Dtos\PostUpdateData;
use Chuoke\Blog\Dtos\PostListData;

class PostController extends Controller
{
    public function index(Request $request, PostList $action)
    {
        $data = new PostListData(
            perPage: (int) $request->get('per_page', 15),
            categoryId: $request->get('category_id'),
            status: $request->get('status'),
            language: $request->get('language'),
            originOnly: $request->boolean('origin_only', true),
        );
        
        $posts = $action->execute($data);

        return Inertia::render('Blog/Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['category_id', 'status', 'language', 'origin_only']),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Admin/Posts/Create', [
            'categories' => \Chuoke\Blog\Models\Category::select('id', 'name')->get(),
            'tags' => \Chuoke\Blog\Models\Tag::select('id', 'name')->get(),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function store(Request $request, PostCreate $action)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string',
            'status' => 'nullable|string|in:draft,published',
            'language' => 'nullable|string|max:10',
        ]);

        $status = $validated['status'] ?? 'draft';

        $data = new PostCreateData(
            title: $validated['title'],
            content: $validated['content'],
            authorId: $request->user()->getKey(),
            summary: $validated['summary'] ?? null,
            categoryId: $request->input('category_id'),
            tagIds: $request->input('tag_ids', []),
            status: $status,
            publishedAt: $status === 'published' ? now()->toDateTimeString() : null,
            coverImageId: $request->input('cover_image_id'),
            language: $validated['language'] ?? config('blog.locale', 'en'),
        );

        $action->execute($data);

        return redirect()->route('blog.admin.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post, PostGet $action)
    {
        return Inertia::render('Blog/Admin/Posts/Edit', [
            'post' => $action->execute($post),
            'categories' => \Chuoke\Blog\Models\Category::select('id', 'name')->get(),
            'tags' => \Chuoke\Blog\Models\Tag::select('id', 'name')->get(),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function update(Request $request, Post $post, PostUpdate $action)
    {
        $data = new PostUpdateData(
            title: $request->input('title'),
            content: $request->input('content'),
            summary: $request->input('summary'),
            categoryId: $request->input('category_id'),
            tagIds: $request->input('tag_ids'),
            coverImageId: $request->input('cover_image_id'),
        );

        $action->execute($post, $data);

        return redirect()->route('blog.admin.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post, PostDelete $action)
    {
        $action->execute($post);
        return redirect()->route('blog.admin.posts.index')->with('success', 'Post deleted successfully.');
    }
}
