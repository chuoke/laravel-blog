<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Category;

class CategoryDelete
{
    public function execute(Category $category): ?bool
    {
        $deleted = $category->delete();

        Blog::forgetFrontCache();

        return $deleted;
    }
}
