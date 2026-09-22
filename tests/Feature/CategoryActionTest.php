<?php

use Chuoke\Blog\Actions\CategoryCreate;
use Chuoke\Blog\Dtos\CategoryCreateData;
use Chuoke\Blog\Models\Category;

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
