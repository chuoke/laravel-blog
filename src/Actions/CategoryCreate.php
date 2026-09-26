<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\CategoryCreateData;
use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Category;
use Illuminate\Support\Str;

class CategoryCreate
{
    public function execute(CategoryCreateData $data): Category
    {
        $slugName = $data->name[config('blog.locale', 'en')] ?? array_values($data->name)[0] ?? 'category';

        $category = Category::create([
            'name' => $data->name,
            'slug' => Str::slug($slugName).'-'.strtolower(Str::random(4)),
            'description' => $data->description,
            'parent_id' => $data->parentId,
            'sort_order' => $data->sortOrder,
        ]);

        Blog::forgetFrontCache();

        return $category;
    }
}
