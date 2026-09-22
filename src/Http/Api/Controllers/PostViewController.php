<?php

namespace Chuoke\Blog\Http\Api\Controllers;

use Illuminate\Routing\Controller;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Actions\PostIncrementViewCount;

class PostViewController extends Controller
{
    public function store(Post $post, PostIncrementViewCount $action)
    {
        $action->execute($post);
        return response()->noContent();
    }
}
