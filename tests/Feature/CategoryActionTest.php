<?php

use Chuoke\Blog\Actions\CategoryCreate;
use Chuoke\Blog\Dtos\CategoryCreateData;
use Chuoke\Blog\Dtos\CategoryUpdateData;
use Chuoke\Blog\Models\Category;

it('preserves positional category DTO arguments when adding an SEO title', function () {
    $createData = new CategoryCreateData(['en' => 'Tech'], ['en' => 'Description'], 12, 3, ['en' => 'SEO title']);
    $updateData = new CategoryUpdateData(['en' => 'Tech'], ['en' => 'Description'], 12, 3, ['en' => 'SEO title']);

    expect($createData->parentId)->toBe(12)
        ->and($createData->sortOrder)->toBe(3)
        ->and($createData->seoTitle)->toBe(['en' => 'SEO title'])
        ->and($updateData->parentId)->toBe(12)
        ->and($updateData->sortOrder)->toBe(3)
        ->and($updateData->seoTitle)->toBe(['en' => 'SEO title']);
});

it('can create a category', function () {
    $action = new CategoryCreate();
    $data = new CategoryCreateData(
        name: ['en' => 'Tech News'],
        description: ['en' => 'Latest tech updates']
    );

    $category = $action->execute($data);

    expect($category)->toBeInstanceOf(Category::class);
    expect($category->name)->toEqual(['en' => 'Tech News']);
    expect($category->slug)->not->toBeNull();

    $this->assertDatabaseHas('blog_categories', [
        'name' => json_encode(['en' => 'Tech News']),
        'description' => json_encode(['en' => 'Latest tech updates']),
    ]);
});
