<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Facades\Blog;

it('treats literal percent/underscore in the search term as literal characters, not wildcards', function () {
    // basePostQuery() eager loads the author relation; point it at a model
    // that actually exists in this package's test environment.
    config(['blog.author_model' => \Illuminate\Foundation\Auth\User::class]);

    $action = new PostCreate();

    // This title contains a literal underscore that should NOT act as a
    // single-character wildcard and match unrelated posts.
    $action->execute(new PostCreateData(
        title: 'Post with under_score',
        content: 'content a',
        authorId: 1,
        status: 'published',
        publishedAt: now()->subDay()->toDateTimeString(),
    ));

    $action->execute(new PostCreateData(
        title: 'Post with underXscore',
        content: 'content b',
        authorId: 1,
        status: 'published',
        publishedAt: now()->subDay()->toDateTimeString(),
    ));

    $results = Blog::paginatedPosts(['search' => 'under_score']);

    expect($results->total())->toBe(1);
    expect($results->items()[0]->title)->toBe('Post with under_score');
});
