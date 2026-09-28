<?php

use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Tag;
use Illuminate\Foundation\Auth\User;

beforeEach(function () {
    config([
        'blog.author_model' => User::class,
        'blog.supported_locales' => ['en' => 'English', 'zh_CN' => '中文'],
    ]);

    $user = new User;
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user);
});

it('renders dedicated category create and edit pages', function () {
    $this->get(route('blog.admin.categories.create'), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Blog/Admin/Categories/Form')
        ->assertJsonPath('props.locales.zh_CN', '中文');

    $this->post(route('blog.admin.categories.store'), [
        'name' => ['en' => 'Guides', 'zh_CN' => ''],
        'description' => ['en' => 'Helpful guides', 'zh_CN' => ''],
        'sort_order' => 3,
    ])->assertRedirect();

    $category = Category::firstOrFail();

    expect($category->name)->toBe(['en' => 'Guides', 'zh_CN' => null])
        ->and($category->description)->toBe(['en' => 'Helpful guides', 'zh_CN' => null]);

    $this->get(route('blog.admin.categories.edit', $category), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Blog/Admin/Categories/Form')
        ->assertJsonPath('props.category.id', $category->id);
});

it('renders dedicated tag create and edit pages', function () {
    $this->get(route('blog.admin.tags.create'), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Blog/Admin/Tags/Form')
        ->assertJsonPath('props.locales.zh_CN', '中文');

    $this->post(route('blog.admin.tags.store'), [
        'name' => ['en' => 'AI Tools', 'zh_CN' => ''],
    ])->assertRedirect();

    $tag = Tag::firstOrFail();

    expect($tag->name)->toBe(['en' => 'AI Tools', 'zh_CN' => null]);

    $this->get(route('blog.admin.tags.edit', $tag), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Blog/Admin/Tags/Form')
        ->assertJsonPath('props.tag.id', $tag->id);
});

it('requires the default locale and rejects unsupported locale keys', function () {
    $this->from(route('blog.admin.categories.create'))
        ->post(route('blog.admin.categories.store'), [
            'name' => ['zh_CN' => '使用指南'],
        ])
        ->assertRedirect(route('blog.admin.categories.create'))
        ->assertSessionHasErrors('name.en');

    $this->from(route('blog.admin.tags.create'))
        ->post(route('blog.admin.tags.store'), [
            'name' => ['en' => 'AI Tools', 'fr' => 'Outils IA'],
        ])
        ->assertRedirect(route('blog.admin.tags.create'))
        ->assertSessionHasErrors('name');
});
