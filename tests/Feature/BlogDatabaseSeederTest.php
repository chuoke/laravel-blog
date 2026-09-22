<?php

use Chuoke\Blog\Database\Seeders\BlogDatabaseSeeder;
use Chuoke\Blog\Models\Post;

it('runs without error and links translations via a shared article_id', function () {
    (new BlogDatabaseSeeder())->run();

    $original = Post::where('slug', 'getting-started-with-laravel-11')->firstOrFail();
    $translation = Post::where('slug', 'getting-started-with-laravel-11-zh')->firstOrFail();

    expect($original->article_id)->toBe($original->id);
    expect($translation->article_id)->toBe($original->article_id);
    expect($translation->isTranslation())->toBeTrue();
});
