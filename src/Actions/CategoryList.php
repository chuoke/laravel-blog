<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryList
{
    public function execute(): Collection
    {
        return Category::orderBy('sort_order')->get();
    }
}
