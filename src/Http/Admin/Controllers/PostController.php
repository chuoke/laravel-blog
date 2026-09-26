<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Actions\PostDelete;
use Chuoke\Blog\Actions\PostGet;
use Chuoke\Blog\Actions\PostList;
use Chuoke\Blog\Actions\PostTogglePin;
use Chuoke\Blog\Actions\PostUpdate;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Dtos\PostListData;
use Chuoke\Blog\Dtos\PostUpdateData;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index(Request $request, PostList $action)
    {
        $data = new PostListData(
            perPage: (int) $request->get('per_page', 15),
            search: $request->filled('search') ? $request->string('search')->trim()->value() : null,
            categoryId: $request->get('category_id'),
            status: $request->get('status'),
            language: $request->get('language'),
            originOnly: $request->boolean('origin_only', true),
            pinnedOnly: $request->boolean('pinned_only'),
            sortBy: $request->get('sort_by', 'id'),
        );

        $posts = $action->execute($data);

        return Inertia::render('Blog/Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'category_id', 'status', 'language', 'origin_only', 'pinned_only', 'sort_by']),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Blog/Admin/Posts/Create', [
            'categories' => Category::select('id', 'name')->get(),
            'tags' => Tag::select('id', 'name')->get(),
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
            'language' => ['nullable', 'string', 'max:10', Rule::in(array_keys(config('blog.supported_locales', ['en' => 'English'])))],
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
            'categories' => Category::select('id', 'name')->get(),
            'tags' => Tag::select('id', 'name')->get(),
            'locales' => config('blog.supported_locales', ['en' => 'English']),
            'translations' => $post->siblings()->select('id', 'language')->get(),
        ]);
    }

    public function createTranslation(Post $post, string $language, PostCreate $action)
    {
        $validated = Validator::validate(['language' => $language], [
            'language' => ['required', 'string', Rule::in(array_keys(config('blog.supported_locales', ['en' => 'English'])))],
        ]);

        $translation = DB::transaction(function () use ($post, $validated, $action): Post {
            $original = Post::query()->lockForUpdate()->findOrFail($post->article_id);
            $existing = $original->siblings()->where('language', $validated['language'])->first();

            if ($existing) {
                return $existing;
            }

            return $action->execute(new PostCreateData(
                title: $original->title,
                content: $original->content,
                authorId: request()->user()->getKey(),
                summary: $original->summary,
                categoryId: $original->category_id,
                tagIds: $original->tags->pluck('id')->all(),
                articleId: $original->article_id,
                coverImageId: $original->cover_image_id,
                sourceType: 'translated',
                language: $validated['language'],
            ));
        });

        return redirect()->route('blog.admin.posts.edit', $translation->getKey());
    }

    public function update(Request $request, Post $post, PostUpdate $action)
    {
        $validated = $request->validate([
            'language' => $post->isTranslation()
                ? ['nullable']
                : [
                    'nullable',
                    'string',
                    'max:10',
                    Rule::in(array_keys(config('blog.supported_locales', ['en' => 'English']))),
                    Rule::unique('blog_posts', 'language')
                        ->where('article_id', $post->article_id)
                        ->ignore($post->id),
                ],
        ]);

        $data = new PostUpdateData(
            title: $request->input('title'),
            content: $request->input('content'),
            summary: $request->input('summary'),
            categoryId: $request->input('category_id'),
            tagIds: $request->input('tag_ids'),
            coverImageId: $request->input('cover_image_id'),
            language: $post->isTranslation() ? null : ($validated['language'] ?? null),
        );

        $action->execute($post, $data);

        return redirect()->route('blog.admin.posts.edit', $post->getKey())->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post, PostDelete $action)
    {
        $action->execute($post);

        return redirect()->route('blog.admin.posts.index')->with('success', 'Post deleted successfully.');
    }

    public function togglePin(Post $post, PostTogglePin $action)
    {
        $action->execute($post);

        return back();
    }
}
