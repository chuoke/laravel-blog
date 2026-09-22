<?php

namespace Chuoke\Blog\Http\Front\Controllers;

use Illuminate\Routing\Controller;
use Chuoke\Blog\Facades\Blog;

class PostController extends Controller
{
    public function index()
    {
        return view('blog::posts.index');
    }

    public function show($slug)
    {
        $post = Blog::post($slug);
        abort_if(!$post, 404);

        Blog::recordView($post);

        return view('blog::posts.show', compact('post'));
    }
}
