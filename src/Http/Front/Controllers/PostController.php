<?php

namespace Chuoke\Blog\Http\Front\Controllers;

use Chuoke\Blog\Facades\Blog;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        return view('blog::posts.index');
    }

    public function show(string $identifier)
    {
        $post = Blog::post(Str::before($identifier, '-'));
        abort_if(! $post, 404);

        Blog::recordView($post);

        return view('blog::posts.show', compact('post'));
    }
}
