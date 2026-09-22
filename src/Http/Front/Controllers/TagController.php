<?php

namespace Chuoke\Blog\Http\Front\Controllers;

use Illuminate\Routing\Controller;
use Chuoke\Blog\Facades\Blog;

class TagController extends Controller
{
    public function show($slug)
    {
        $tag = Blog::tag($slug);
        abort_if(!$tag, 404);

        return view('blog::tags.show', compact('tag'));
    }
}
