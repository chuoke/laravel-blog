<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Actions\PostGet;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Models\Attachment;
use Chuoke\Blog\Models\Post;

it('can create a post', function () {
    $action = new PostCreate();
    $data = new PostCreateData(
        title: 'Test Post',
        content: '# Hello World',
        authorId: 1,
        status: 'published',
    );

    $post = $action->execute($data);

    expect($post)->toBeInstanceOf(Post::class);
    expect($post->title)->toBe('Test Post');
    expect($post->uid)->not->toBeNull();
    expect(strlen($post->uid))->toBe(10);

    // article_id should be set to its own id for original posts
    expect($post->article_id)->toBe($post->id);
    expect($post->isOriginal())->toBeTrue();
    expect($post->isTranslation())->toBeFalse();

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'Test Post',
        'author_id' => 1,
        'status' => 'published',
    ]);
});

it('can create a translation post sharing the same article_id', function () {
    $action = new PostCreate();

    // Create original post
    $original = $action->execute(new PostCreateData(
        title: 'Original Post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));

    // Create translation
    $translation = $action->execute(new PostCreateData(
        title: '翻译文章',
        content: '翻译内容',
        authorId: 1,
        language: 'zh-CN',
        articleId: $original->article_id,
    ));

    expect($translation->article_id)->toBe($original->article_id);
    expect($translation->isTranslation())->toBeTrue();
    expect($original->fresh()->siblings)->toHaveCount(2);
});

it('loads the cover image when retrieving a post for editing', function () {
    config(['blog.author_model' => \Illuminate\Foundation\Auth\User::class]);

    $cover = Attachment::create([
        'path' => 'blog/covers/cover.webp',
        'file_name' => 'cover.webp',
    ]);

    $post = (new PostCreate)->execute(new PostCreateData(
        title: 'Post with a cover',
        content: 'Content',
        authorId: 1,
        coverImageId: $cover->id,
    ));

    $post = (new PostGet)->execute($post);

    expect($post->relationLoaded('coverImage'))->toBeTrue()
        ->and($post->coverImage?->id)->toBe($cover->id);
});
