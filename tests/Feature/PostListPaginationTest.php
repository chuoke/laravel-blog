<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Actions\PostList;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Dtos\PostListData;
use Illuminate\Foundation\Auth\User;

beforeEach(function () {
    config(['blog.author_model' => User::class]);
});

it('preserves the active filters in pagination links', function () {
    foreach (range(1, 2) as $number) {
        (new PostCreate())->execute(new PostCreateData(
            title: "Filtered post {$number}",
            content: 'Content',
            authorId: 1,
            language: 'en',
        ));
    }

    request()->query->replace([
        'search' => 'Filtered',
        'language' => 'en',
        'origin_only' => '1',
        'sort_by' => 'updated_at',
    ]);

    $posts = (new PostList())->execute(new PostListData(
        perPage: 1,
        search: 'Filtered',
        language: 'en',
        originOnly: true,
        sortBy: 'updated_at',
    ));

    expect($posts->nextPageUrl())
        ->toContain('page=2')
        ->toContain('search=Filtered')
        ->toContain('language=en')
        ->toContain('origin_only=1')
        ->toContain('sort_by=updated_at');
});
