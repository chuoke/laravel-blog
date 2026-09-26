<?php

use Chuoke\Blog\Actions\CategoryCreate;
use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\BlogManager;
use Chuoke\Blog\Dtos\CategoryCreateData;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Models\Post;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    config([
        'blog.author_model' => \Illuminate\Foundation\Auth\User::class,
        'blog.cache.front_ttl' => 300,
    ]);
});

it('caches front page posts until the cache is invalidated', function () {
    (new PostCreate())->execute(new PostCreateData(
        title: 'First post',
        content: 'Content',
        authorId: 1,
        status: 'published',
        publishedAt: now(),
    ));

    expect((new BlogManager())->latestPosts()->pluck('title')->all())->toBe(['First post']);

    Post::create([
        'uid' => 'second-post',
        'author_id' => 1,
        'title' => 'Second post',
        'slug' => 'second-post',
        'content' => 'Content',
        'status' => 'published',
        'language' => app()->getLocale(),
        'published_at' => now(),
    ]);

    expect((new BlogManager())->latestPosts()->pluck('title')->all())->toBe(['First post']);

    (new BlogManager())->forgetFrontCache();

    expect((new BlogManager())->latestPosts()->pluck('title')->all())->toHaveCount(2);
});

it('invalidates cached front collections after a content change', function () {
    expect((new BlogManager())->categories())->toBeEmpty();

    (new CategoryCreate())->execute(new CategoryCreateData(name: ['en' => 'News']));

    expect((new BlogManager())->categories()->pluck('name')->all())->toBe([['en' => 'News']]);
});
