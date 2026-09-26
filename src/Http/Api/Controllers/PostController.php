<?php

namespace Chuoke\Blog\Http\Api\Controllers;

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Actions\PostDelete;
use Chuoke\Blog\Actions\PostGet;
use Chuoke\Blog\Actions\PostList;
use Chuoke\Blog\Actions\PostUpdate;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Dtos\PostListData;
use Chuoke\Blog\Dtos\PostUpdateData;
use Chuoke\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

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
        );

        return $action->execute($data);
    }

    public function store(Request $request, PostCreate $action)
    {
        // Prefer the authenticated user as the author when available, rather
        // than trusting a client-supplied author_id (which would let callers
        // impersonate other authors). Only fall back to an explicit
        // author_id for unauthenticated/service-to-service requests.
        $authenticatedAuthorId = $request->user()?->getAuthIdentifier();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'author_id' => $authenticatedAuthorId ? 'nullable' : 'required',
        ]);

        $data = new PostCreateData(
            title: $validated['title'],
            content: $validated['content'],
            authorId: $authenticatedAuthorId ?? $validated['author_id'],
            categoryId: $request->input('category_id'),
            tagIds: $request->input('tag_ids', []),
        );

        $post = $action->execute($data);

        return response()->json($post, 201);
    }

    public function show(Post $post, PostGet $action)
    {
        return $action->execute($post);
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

        $post = $action->execute($post, $data);

        return response()->json($post);
    }

    public function destroy(Post $post, PostDelete $action)
    {
        $action->execute($post);

        return response()->noContent();
    }
}
