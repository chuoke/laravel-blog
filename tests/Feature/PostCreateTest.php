<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Dtos\PostCreateData;
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
