<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;

it('renders the blog homepage using the resolved theme views', function () {
    config(['blog.author_model' => User::class]);

    $response = $this->get(route('blog.home'));

    $response->assertOk();
});

it('uses matching article limits for both newsroom sidebars', function () {
    Blog::shouldReceive('latestPosts')->once()->with(12)->andReturn(new Collection);
    Blog::shouldReceive('popularPosts')->once()->with(3)->andReturn(new Collection);
    Blog::shouldReceive('pinnedPosts')->once()->with(1)->andReturn(new Collection);
    Blog::shouldReceive('formatDate')->once()->andReturn('October 8, 2026');
    Blog::shouldReceive('categories')->once()->andReturn(new Collection);

    $this->app['view']->getFinder()->prependNamespace('blog', __DIR__.'/../../resources/views/themes/newsroom');

    $this->view('blog::home')->assertSee(__('blog::ui.trending'));
});

it('fills the magazine issue sidebar with latest articles when only the lead is pinned', function () {
    $lead = new Post(['id' => 1, 'uid' => 'lead', 'slug' => 'lead', 'title' => 'Lead story', 'published_at' => now(), 'language' => 'en']);
    $firstFallback = new Post(['id' => 2, 'uid' => 'first', 'slug' => 'first', 'title' => 'First fallback story', 'published_at' => now(), 'language' => 'en']);
    $secondFallback = new Post(['id' => 3, 'uid' => 'second', 'slug' => 'second', 'title' => 'Second fallback story', 'published_at' => now(), 'language' => 'en']);

    $lead->id = 1;
    $firstFallback->id = 2;
    $secondFallback->id = 3;

    foreach ([$lead, $firstFallback, $secondFallback] as $post) {
        $post->setRelation('category', null);
        $post->setRelation('coverImage', null);
    }

    Blog::shouldReceive('pinnedPosts')->once()->with(3)->andReturn(new Collection([$lead]));
    Blog::shouldReceive('latestPosts')->once()->with(8)->andReturn(new Collection([$lead, $firstFallback, $secondFallback]));
    Blog::shouldReceive('popularPosts')->once()->with(5)->andReturn(new Collection);
    Blog::shouldReceive('formatDate')->andReturn('October 8, 2026');
    Blog::shouldReceive('categories')->andReturn(new Collection);
    Blog::shouldReceive('categoriesWithCount')->once()->andReturn(new Collection);
    Blog::shouldReceive('tagsWithCount')->once()->andReturn(new Collection);

    $this->app['view']->getFinder()->prependNamespace('blog', __DIR__.'/../../resources/views/themes/magazine');

    $this->view('blog::home')
        ->assertSee('First fallback story')
        ->assertSee('Second fallback story');
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
