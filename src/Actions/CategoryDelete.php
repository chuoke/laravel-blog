<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Category;

class CategoryDelete
{
    public function execute(Category $category): ?bool
    {
        return $category->delete();
    }
}
