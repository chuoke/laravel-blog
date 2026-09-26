<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Dtos\PostCreateData;
use Illuminate\Foundation\Auth\User;

it('renders the blog homepage using the resolved theme views', function () {
    config(['blog.author_model' => User::class]);

    $response = $this->get(route('blog.home'));

    $response->assertOk();
});

it('renders the posts index page', function () {
    config(['blog.author_model' => User::class]);

    $response = $this->get(route('blog.posts.index'));

    $response->assertOk();
});

it('shows posts by their public uid and slug', function () {
    config(['blog.author_model' => User::class]);

    $post = (new PostCreate)->execute(new PostCreateData(
        title: 'A published post',
        content: 'content',
        authorId: 1,
        status: 'published',
        publishedAt: now(),
    ));

    $url = route('blog.posts.show', $post);

    expect($url)->toEndWith("{$post->uid}-{$post->slug}");

    $this->get($url)
        ->assertOk()
        ->assertSeeText($post->title);

    $this->get(route('blog.posts.show', $post->uid))->assertNotFound();
    $this->get(route('blog.posts.show', $post->slug))->assertNotFound();
});

it('renders the homepage sidebar archives list when published posts exist', function () {
    config(['blog.author_model' => User::class]);

    (new PostCreate)->execute(new PostCreateData(
        title: 'A published post',
        content: 'content',
        authorId: 1,
        status: 'published',
        publishedAt: now()->subDay()->toDateTimeString(),
    ));

    // Exercises BlogManager::archives(), whose entries the sidebar accesses
    // as objects ($archive->year / ->month / ->count).
    $response = $this->get(route('blog.home'));

    $response->assertOk();
});

it('lets a host app override a theme view by publishing it to resource_path', function () {
    config(['blog.author_model' => User::class]);

    $overrideDir = resource_path('views/vendor/blog/themes/default');
    $overrideFile = $overrideDir.'/home.blade.php';

    if (! is_dir($overrideDir)) {
        mkdir($overrideDir, 0777, true);
    }
    file_put_contents($overrideFile, "@extends('blog::layout')\n@section('content')\nCUSTOM_THEME_OVERRIDE_MARKER\n@endsection\n");

    try {
        $response = $this->get(route('blog.home'));

        $response->assertOk();
        $response->assertSeeText('CUSTOM_THEME_OVERRIDE_MARKER');
    } finally {
        @unlink($overrideFile);
    }
});
