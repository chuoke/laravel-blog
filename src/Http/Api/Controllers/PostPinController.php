<?php

namespace Chuoke\Blog\Http\Api\Controllers;

use Illuminate\Routing\Controller;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Actions\PostTogglePin;

class PostPinController extends Controller
{
    public function update(Post $post, PostTogglePin $action)
    {
        return response()->json($action->execute($post));
    }
}
