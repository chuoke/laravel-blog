<?php

namespace Chuoke\Blog\Http\Front\Controllers;

use Illuminate\Routing\Controller;
use Chuoke\Blog\Facades\Blog;

class CategoryController extends Controller
{
    public function show($slug)
    {
        $category = Blog::category($slug);
        abort_if(!$category, 404);

        return view('blog::categories.show', compact('category'));
    }
}
