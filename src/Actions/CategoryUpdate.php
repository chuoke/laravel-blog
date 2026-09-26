<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\CategoryUpdateData;
use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Category;

class CategoryUpdate
{
    public function execute(Category $category, CategoryUpdateData $data): Category
    {
        $updateAttributes = array_filter([
            'name' => $data->name,
            'description' => $data->description,
            'parent_id' => $data->parentId,
            'sort_order' => $data->sortOrder,
        ], fn ($value) => $value !== null);

        if (! empty($updateAttributes)) {
            $category->update($updateAttributes);
        }

        Blog::forgetFrontCache();

        return $category->fresh();
    }
}
