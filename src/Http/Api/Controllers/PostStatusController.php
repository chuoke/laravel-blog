<?php

namespace Chuoke\Blog\Http\Api\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Actions\PostPublish;
use Chuoke\Blog\Actions\PostArchive;

class PostStatusController extends Controller
{
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'status' => 'required|string|in:published,archived',
        ]);

        $action = match ($request->input('status')) {
            'published' => app(PostPublish::class),
            'archived' => app(PostArchive::class),
        };

        return response()->json($action->execute($post));
    }
}
