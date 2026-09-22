<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Category;

class CategoryGet
{
    public function execute(Category $category): Category
    {
        return $category->load('children');
    }
}
