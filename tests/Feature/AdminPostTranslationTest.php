<?php

use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Actions\PostDelete;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Models\Attachment;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Models\Tag;
use Illuminate\Foundation\Auth\User;

beforeEach(function () {
    config([
        'blog.author_model' => User::class,
        'blog.supported_locales' => ['en' => 'English', 'zh' => 'Chinese'],
    ]);

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user);
});

it('creates a draft translation from the original post', function () {
    $category = Category::create(['name' => ['en' => 'News'], 'slug' => 'news']);
    $tag = Tag::create(['name' => ['en' => 'Laravel'], 'slug' => 'laravel']);
    $cover = Attachment::create(['path' => 'cover.jpg', 'file_name' => 'cover.jpg']);
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        summary: 'Original summary',
        categoryId: $category->id,
        tagIds: [$tag->id],
        coverImageId: $cover->id,
        language: 'en',
        status: 'published',
    ));

    $this->post(route('blog.admin.posts.translations.store', [$original->getKey(), 'zh']))
        ->assertRedirect();

    $translation = Post::where('article_id', $original->article_id)->where('language', 'zh')->firstOrFail();

    expect($translation)
        ->title->toBe($original->title)
        ->content->toBe($original->content)
        ->summary->toBe($original->summary)
        ->category_id->toBe($category->id)
        ->cover_image_id->toBe($cover->id)
        ->status->toBe('draft')
        ->source_type->toBe('translated')
        ->isTranslation()->toBeTrue();
    expect($translation->tags->modelKeys())->toBe([$tag->id]);
});

it('redirects to an existing translation instead of creating another one', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));

    $this->post(route('blog.admin.posts.translations.store', [$original->getKey(), 'zh']))
        ->assertRedirect(route('blog.admin.posts.edit', $translation->getKey()));

    expect(Post::where('article_id', $original->article_id)->count())->toBe(2);
});

it('rejects an unsupported translation language', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));

    $this->from(route('blog.admin.posts.edit', $original->getKey()))
        ->post(route('blog.admin.posts.translations.store', [$original->getKey(), 'fr']))
        ->assertRedirect(route('blog.admin.posts.edit', $original->getKey()))
        ->assertSessionHasErrors('language');
});

it('does not allow a translation language to change after creation', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));

    $this->put(route('blog.admin.posts.update', $translation->getKey()), ['language' => 'en'])
        ->assertRedirect(route('blog.admin.posts.edit', $translation->getKey()));

    expect($translation->fresh()->language)->toBe('zh');
});

it('allows the original language to reuse a soft-deleted translation language', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));
    $translation->delete();

    $this->put(route('blog.admin.posts.update', $original->getKey()), ['language' => 'zh'])
        ->assertRedirect(route('blog.admin.posts.edit', $original->getKey()))
        ->assertSessionDoesntHaveErrors('language');

    expect($original->fresh()->language)->toBe('zh')
        ->and(Post::withTrashed()->find($translation->id))->toBeNull();
});

it('publishes a draft while saving its current edits', function () {
    $post = (new PostCreate())->execute(new PostCreateData(
        title: 'Draft post',
        content: 'Draft content',
        authorId: 1,
        language: 'en',
    ));

    $this->put(route('blog.admin.posts.update', $post->getKey()), [
        'title' => 'Published post',
        'content' => 'Published content',
        'status' => 'published',
    ])->assertRedirect(route('blog.admin.posts.edit', $post->getKey()));

    expect($post->fresh())
        ->title->toBe('Published post')
        ->content->toBe('Published content')
        ->status->toBe('published')
        ->published_at->not->toBeNull();
});

it('does not pass a click event as a draft save status', function () {
    $page = file_get_contents(__DIR__.'/../../resources/js/Pages/Blog/Admin/Posts/Edit.vue');

    expect($page)
        ->toContain('@click="submit()"')
        ->toContain('@click="deleteTranslation(translation(code)!)"');
});

it('preserves a published date when saving published post edits', function () {
    $publishedAt = now()->subHour()->startOfSecond();
    $post = (new PostCreate())->execute(new PostCreateData(
        title: 'Published post',
        content: 'Published content',
        authorId: 1,
        language: 'en',
        status: 'published',
        publishedAt: $publishedAt->toDateTimeString(),
    ));

    $this->put(route('blog.admin.posts.update', $post->getKey()), [
        'title' => 'Updated published post',
    ])->assertRedirect(route('blog.admin.posts.edit', $post->getKey()));

    expect($post->fresh())
        ->status->toBe('published')
        ->published_at->toDateTimeString()->toBe($publishedAt->toDateTimeString());
});

it('does not allow the API to change a translation language', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));

    $this->putJson(route('blog.api.posts.update', $translation->getKey()), ['language' => 'en'])
        ->assertOk();

    expect($translation->fresh()->language)->toBe('zh');
});

it('only allows the original to maintain the article taxonomy', function () {
    $firstCategory = Category::create(['name' => ['en' => 'News'], 'slug' => 'news']);
    $secondCategory = Category::create(['name' => ['en' => 'Guides'], 'slug' => 'guides']);
    $firstTag = Tag::create(['name' => ['en' => 'Laravel'], 'slug' => 'laravel']);
    $secondTag = Tag::create(['name' => ['en' => 'Vue'], 'slug' => 'vue']);
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        categoryId: $firstCategory->id,
        tagIds: [$firstTag->id],
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        categoryId: $firstCategory->id,
        tagIds: [$firstTag->id],
        language: 'zh',
        articleId: $original->article_id,
    ));

    $this->put(route('blog.admin.posts.update', $original->getKey()), [
        'category_id' => $secondCategory->id,
        'tag_ids' => [$secondTag->id],
    ])->assertRedirect();

    expect($translation->fresh())
        ->category_id->toBe($secondCategory->id)
        ->tags->modelKeys()->toBe([$secondTag->id]);

    $this->put(route('blog.admin.posts.update', $translation->getKey()), [
        'category_id' => $firstCategory->id,
        'tag_ids' => [$firstTag->id],
    ])->assertRedirect();

    expect($original->fresh())
        ->category_id->toBe($secondCategory->id)
        ->tags->modelKeys()->toBe([$secondTag->id]);
    expect($translation->fresh()->category_id)->toBe($secondCategory->id);
});

it('soft deletes the article group when its original post is deleted', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));

    (new PostDelete())->execute($original);

    expect($original->fresh()->trashed())->toBeTrue()
        ->and($translation->fresh()->trashed())->toBeTrue();
});

it('deletes a translation and returns to its original post', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));

    $this->delete(route('blog.admin.posts.destroy', $translation->getKey()))
        ->assertRedirect(route('blog.admin.posts.edit', $original->getKey()));

    expect($translation->fresh()->trashed())->toBeTrue();

    $this->post(route('blog.admin.posts.translations.store', [$original->getKey(), 'zh']))
        ->assertRedirect();

    expect(Post::query()->where('article_id', $original->article_id)->where('language', 'zh')->count())->toBe(1);
});

it('shares article translations with the post edit page', function () {
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original post',
        content: 'Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Translated post',
        content: 'Translated content',
        authorId: 1,
        language: 'zh',
        articleId: $original->article_id,
    ));

    $this->get(route('blog.admin.posts.edit', $original->getKey()), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Blog/Admin/Posts/Edit')
        ->assertJsonPath('props.post.is_translation', false)
        ->assertJsonFragment(['id' => $translation->id, 'language' => 'zh']);

    $this->get(route('blog.admin.posts.edit', $translation->getKey()), ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('props.originalLanguage', 'en');
});
