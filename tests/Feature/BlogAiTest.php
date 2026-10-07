<?php

use Chuoke\Blog\Actions\AttachmentUpload;
use Chuoke\Blog\Actions\BlogContentReview;
use Chuoke\Blog\Actions\BlogContentTranslate;
use Chuoke\Blog\Actions\BlogCoverAttachmentStore;
use Chuoke\Blog\Actions\BlogCoverGenerate;
use Chuoke\Blog\Actions\BlogSlugGenerate;
use Chuoke\Blog\Actions\BlogSummaryGenerate;
use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Ai\BlogContentReviewAgent;
use Chuoke\Blog\Ai\BlogContentTranslateAgent;
use Chuoke\Blog\Ai\BlogSlugGenerateAgent;
use Chuoke\Blog\Ai\BlogSummaryGenerateAgent;
use Chuoke\Blog\Contracts\AttachmentPathGenerator;
use Chuoke\Blog\Contracts\BlogAiAuthorizer;
use Chuoke\Blog\Contracts\BlogCoverGenerator;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Events\BlogCoverGenerated;
use Chuoke\Blog\Events\BlogCoverGenerationFailed;
use Chuoke\Blog\Exceptions\BlogAiUnavailable;
use Chuoke\Blog\Jobs\BlogCoverGenerateJob;
use Chuoke\Blog\Models\Attachment;
use Chuoke\Blog\Models\CoverGeneration;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;
use Laravel\Ai\Providers\OpenAiProvider;

beforeEach(function (): void {
    config([
        'blog.ai.enabled' => true,
        'blog.ai.text.provider' => 'openai',
        'blog.ai.text.model' => 'gpt-5-mini',
        'blog.ai.image.provider' => 'openai',
        'blog.ai.image.model' => 'gpt-image-1',
        'blog.attachment.disk' => 'public',
    ]);
});

it('resolves the default AI cover generator from configuration', function (): void {
    expect(app(BlogCoverGenerator::class))->toBeInstanceOf(BlogCoverGenerate::class);
});

it('generates a summary using the configured text provider and model', function (): void {
    config(['blog.ai.prompts.summary' => 'Prefer a practical, calm editorial tone.']);
    BlogSummaryGenerateAgent::fake([['summary' => 'A practical guide to building a focused editorial workflow.']]);

    $summary = (new BlogSummaryGenerate())->execute([
        'title' => 'Editorial workflow',
        'content' => 'A clear process for planning, drafting, and reviewing blog posts.',
        'language' => 'en',
    ]);

    expect($summary)->toBe('A practical guide to building a focused editorial workflow.');

    BlogSummaryGenerateAgent::assertPrompted(
        fn ($prompt): bool => str_contains($prompt->prompt, 'Article language: en')
            && $prompt->provider instanceof OpenAiProvider
            && $prompt->model === 'gpt-5-mini',
    );

    expect((new BlogSummaryGenerateAgent())->instructions())
        ->toContain('Prefer a practical, calm editorial tone.');
});

it('generates a normalized slug using the configured text provider and model', function (): void {
    config(['blog.ai.prompts.slug' => 'Prefer terms familiar to product discovery audiences.']);
    BlogSlugGenerateAgent::fake([['slug' => 'chan-pin-fa-xian-zhi-nan']]);

    $slug = (new BlogSlugGenerate())->execute([
        'title' => '产品发现指南',
        'content' => 'A guide to discovering useful products.',
        'language' => 'zh_CN',
    ]);

    expect($slug)->toBe('chan-pin-fa-xian-zhi-nan');

    BlogSlugGenerateAgent::assertPrompted(
        fn ($prompt): bool => str_contains($prompt->prompt, 'Article language: zh_CN')
            && $prompt->provider instanceof OpenAiProvider
            && $prompt->model === 'gpt-5-mini',
    );

    expect((new BlogSlugGenerateAgent())->instructions())
        ->toContain('for Chinese, prefer concise Hanyu Pinyin')
        ->toContain('Prefer terms familiar to product discovery audiences.');
});

it('returns a structured editorial review', function (): void {
    app()->setLocale('zh_CN');

    BlogContentReviewAgent::fake([[
        'score' => 82,
        'decision' => 'needs_revision',
        'summary' => 'The structure is clear, but the conclusion needs supporting evidence.',
        'strengths' => ['Clear topic'],
        'issues' => ['Add sources for the central claim'],
    ]]);

    $review = (new BlogContentReview())->execute([
        'title' => 'Review this post',
        'content' => 'Article content for editorial review.',
        'language' => 'en',
    ]);

    expect($review)
        ->toMatchArray([
            'score' => 82,
            'decision' => 'needs_revision',
            'strengths' => ['Clear topic'],
            'issues' => ['Add sources for the central claim'],
        ]);

    expect((new BlogContentReviewAgent())->instructions())
        ->toContain('supplied response language')
        ->toContain('people-first standards')
        ->toContain('Do not reward or penalize length, page count, or word count.')
        ->toContain('Reject search-ranking shortcuts.');

    BlogContentReviewAgent::assertPrompted(
        fn ($prompt): bool => str_contains($prompt->prompt, 'Response language: zh_CN')
            && str_contains($prompt->prompt, 'Article language: en'),
    );
});

it('translates an article with the configured text provider and model', function (): void {
    config(['blog.ai.prompts.translation' => 'Keep product names in English.']);
    BlogContentTranslateAgent::fake([[
        'title' => 'Translated title',
        'summary' => 'Translated summary',
        'content' => '# Translated content',
        'slug' => 'fan-yi-biao-ti',
    ]]);

    $translation = (new BlogContentTranslate())->execute([
        'title' => 'Original title',
        'summary' => 'Original summary',
        'content' => '# Original content',
        'sourceLanguage' => 'en',
        'targetLanguage' => 'zh_CN',
    ]);

    expect($translation)->toBe([
        'title' => 'Translated title',
        'summary' => 'Translated summary',
        'content' => '# Translated content',
        'slug' => 'fan-yi-biao-ti',
    ]);

    BlogContentTranslateAgent::assertPrompted(
        fn ($prompt): bool => str_contains($prompt->prompt, 'Source language: en')
            && str_contains($prompt->prompt, 'Target language: zh_CN')
            && $prompt->provider instanceof OpenAiProvider
            && $prompt->model === 'gpt-5-mini',
    );

    expect((new BlogContentTranslateAgent())->instructions())
        ->toContain('for Chinese prefer Hanyu Pinyin')
        ->toContain('Keep product names in English.');
});

it('translates from the original article for a translation draft', function (): void {
    config(['blog.author_model' => User::class]);
    $original = (new PostCreate())->execute(new PostCreateData(
        title: 'Original title',
        summary: 'Original summary',
        content: '# Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate())->execute(new PostCreateData(
        title: 'Existing translation',
        content: 'Existing content',
        authorId: 1,
        articleId: $original->id,
        language: 'zh_CN',
    ));

    BlogContentTranslateAgent::fake([[
        'title' => '翻译标题',
        'summary' => '翻译摘要',
        'content' => '# 翻译内容',
        'slug' => 'fan-yi-biao-ti',
    ]]);

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user)
        ->post(route('blog.admin.posts.ai.translate', $original->getKey()))
        ->assertUnprocessable();

    $response = $this->actingAs($user)
        ->post(route('blog.admin.posts.ai.translate', $translation->getKey()));

    $response
        ->assertSuccessful()
        ->assertJson([
            'title' => '翻译标题',
            'summary' => '翻译摘要',
            'content' => '# 翻译内容',
            'slug' => 'fan-yi-biao-ti',
        ]);

    BlogContentTranslateAgent::assertPrompted(
        fn ($prompt): bool => $prompt->contains('Original title')
            && $prompt->contains('Target language: zh_CN'),
    );
});

it('generates and stores a resized cover with the configured image provider and model', function (): void {
    Storage::fake('public');
    config(['blog.ai.prompts.cover' => 'Use a warm editorial color palette.']);
    Image::fake([base64_encode(largePng())]);

    $attachment = app(BlogCoverGenerate::class)->execute([
        'title' => 'Generated cover',
        'content' => 'A detailed article about cover image generation.',
        'language' => 'en',
    ]);

    Storage::disk('public')->assertExists($attachment->path);
    expect($attachment->width)->toBe(1920)
        ->and($attachment->height)->toBe(960)
        ->and($attachment->size)->toBeGreaterThan(0);

    Image::assertGenerated(
        fn ($prompt): bool => $prompt->provider instanceof OpenAiProvider
            && $prompt->model === 'gpt-image-1'
            && $prompt->contains('1536x1024 (3:2) landscape')
            && $prompt->contains('specific article details rather than generic filler imagery')
            && $prompt->contains('copyright-risk elements')
            && $prompt->contains('Use a warm editorial color palette.'),
    );
});

it('does not configure an AI cover generation request timeout', function (): void {
    expect(config('blog.ai.image.timeout'))->toBeNull();
});

it('queues cover generation and returns only the caller task status', function (): void {
    config([
        'blog.author_model' => User::class,
        'blog.ai.image_middleware' => [],
    ]);
    Queue::fake();

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $response = $this->actingAs($user)
        ->post(route('blog.admin.ai.cover'), [
            'title' => 'Queued cover',
            'content' => 'The cover is generated in a background job.',
            'language' => 'en',
        ]);

    $response
        ->assertAccepted()
        ->assertJsonPath('status', 'pending');

    $coverGeneration = CoverGeneration::query()->findOrFail($response->json('id'));

    expect($coverGeneration->user_id)->toBe('1')
        ->and($coverGeneration->data['title'])->toBe('Queued cover');

    Queue::assertPushed(BlogCoverGenerateJob::class, fn (BlogCoverGenerateJob $job): bool => $job->coverGenerationId === $coverGeneration->id
        && $job->queue === 'ai-image'
        && $job->timeout === 0);

    $this->actingAs($user)
        ->post(route('blog.admin.ai.cover'), [
            'title' => 'Queued cover',
            'content' => 'The cover is generated in a background job.',
            'language' => 'en',
        ])
        ->assertAccepted()
        ->assertJsonPath('id', $coverGeneration->id);

    $this->actingAs($user)
        ->post(route('blog.admin.ai.cover'), [
            'title' => 'Another cover',
            'content' => 'This request must not reuse another article cover.',
            'language' => 'en',
        ])
        ->assertConflict();

    Queue::assertPushedTimes(BlogCoverGenerateJob::class, 1);

    $attachment = Attachment::create(['path' => 'cover.webp', 'file_name' => 'cover.webp']);
    $coverGeneration->update(['status' => 'completed', 'attachment_id' => $attachment->id]);

    $this->actingAs($user)
        ->get(route('blog.admin.ai.cover.status', $coverGeneration))
        ->assertSuccessful()
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('attachment.id', $attachment->id);

    $otherUser = new User();
    $otherUser->forceFill(['id' => 2])->exists = true;

    $this->actingAs($otherUser)
        ->get(route('blog.admin.ai.cover.status', $coverGeneration))
        ->assertNotFound();
});

it('stores a generated attachment when a queued cover job completes', function (): void {
    Event::fake();

    $attachment = Attachment::create(['path' => 'cover.webp', 'file_name' => 'cover.webp']);
    $coverGeneration = CoverGeneration::create([
        'user_id' => '1',
        'request_hash' => hash('sha256', 'queued-cover'),
        'data' => ['title' => 'Queued cover', 'content' => null, 'language' => 'en'],
    ]);

    $generator = new class($attachment) implements BlogCoverGenerator
    {
        public function __construct(private readonly Attachment $attachment)
        {
        }

        public function execute(array $data): Attachment
        {
            return $this->attachment;
        }
    };

    (new BlogCoverGenerateJob($coverGeneration->id))->handle($generator);

    expect($coverGeneration->refresh())
        ->status->toBe('completed')
        ->attachment_id->toBe($attachment->id)
        ->is_active->toBeNull()
        ->data->toBeNull();

    Event::assertDispatched(BlogCoverGenerated::class, fn (BlogCoverGenerated $event): bool => $event->coverGeneration->is($coverGeneration));

    $alreadyCompletedGenerator = new class() implements BlogCoverGenerator
    {
        public bool $called = false;

        public function execute(array $data): Attachment
        {
            $this->called = true;

            throw new RuntimeException('Completed tasks must not run again.');
        }
    };

    (new BlogCoverGenerateJob($coverGeneration->id))->handle($alreadyCompletedGenerator);

    expect($alreadyCompletedGenerator->called)->toBeFalse();
});

it('keeps a long-running cover generation available for status polling', function (): void {
    config(['blog.author_model' => User::class]);

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $coverGeneration = CoverGeneration::create([
        'user_id' => '1',
        'request_hash' => hash('sha256', 'expired-cover'),
        'data' => ['title' => 'Queued cover', 'content' => 'Draft content', 'language' => 'en'],
    ]);
    $coverGeneration->update(['created_at' => now()->subMinutes(11)]);

    $this->actingAs($user)
        ->get(route('blog.admin.ai.cover.status', $coverGeneration))
        ->assertSuccessful()
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('reason', null);

    expect($coverGeneration->refresh())
        ->is_active->toBe(1)
        ->data->toBeArray()
        ->failure_reason->toBeNull();
});

it('marks a queued cover generation as failed after the job fails', function (): void {
    Event::fake();

    config(['blog.author_model' => User::class]);

    $coverGeneration = CoverGeneration::create([
        'user_id' => '1',
        'request_hash' => hash('sha256', 'failed-cover'),
        'status' => 'processing',
        'data' => ['title' => 'Queued cover', 'content' => null, 'language' => 'en'],
    ]);

    (new BlogCoverGenerateJob($coverGeneration->id))->failed(new RuntimeException('Provider unavailable'));

    expect($coverGeneration->refresh())
        ->status->toBe('failed')
        ->is_active->toBeNull()
        ->data->toBeNull()
        ->failure_reason->toBe('unavailable');

    Event::assertDispatched(BlogCoverGenerationFailed::class, fn (BlogCoverGenerationFailed $event): bool => $event->coverGeneration->is($coverGeneration));

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user)
        ->get(route('blog.admin.ai.cover.status', $coverGeneration))
        ->assertSuccessful()
        ->assertJsonPath('reason', 'unavailable');
});

it('optimizes a manually uploaded cover without requiring AI', function (): void {
    Storage::fake('public');

    $attachment = app(BlogCoverAttachmentStore::class)->fromContent(largePng(), 'image/png', 'cover.png');

    Storage::disk('public')->assertExists($attachment->path);
    expect($attachment->width)->toBe(1920)
        ->and($attachment->height)->toBe(960)
        ->and(Attachment::query()->count())->toBe(1);
});

it('uses the configured path generator for attachments and covers', function (): void {
    Storage::fake('public');
    config(['blog.attachment.path_generator' => TestAttachmentPathGenerator::class]);

    $attachment = app(AttachmentUpload::class)->execute(UploadedFile::fake()->image('article-image.jpg'));
    $cover = app(BlogCoverAttachmentStore::class)->fromContent(largePng(), 'image/png', 'article-cover.png');

    expect($attachment->path)->toBe('custom/article-image.jpg')
        ->and($cover->path)->toStartWith('custom/article-cover.')
        ->and(Storage::disk('public')->exists($attachment->path))->toBeTrue()
        ->and(Storage::disk('public')->exists($cover->path))->toBeTrue();
});

it('rejects attachment paths outside the configured disk', function (): void {
    app()->instance(AttachmentPathGenerator::class, new class() implements AttachmentPathGenerator
    {
        public function generate(string $fileName, string $extension, ?string $directory = null): string
        {
            return '../outside.'.$extension;
        }
    });

    app(AttachmentUpload::class)->execute(UploadedFile::fake()->image('article-image.jpg'));
})->throws(InvalidArgumentException::class, 'must be relative');

it('requires blog AI to be explicitly enabled', function (): void {
    config(['blog.ai.enabled' => false]);

    (new BlogSummaryGenerate())->execute([
        'title' => 'Disabled AI',
        'content' => null,
        'language' => 'en',
    ]);
})->throws(BlogAiUnavailable::class, 'Blog AI is disabled');

it('does not expose AI routes while blog AI is disabled', function (): void {
    config([
        'blog.author_model' => User::class,
        'blog.ai.enabled' => false,
    ]);

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user)
        ->post(route('blog.admin.ai.summary'), ['title' => 'Draft'])
        ->assertNotFound();
});

it('uses the configured AI authorizer', function (): void {
    config([
        'blog.author_model' => User::class,
        'blog.ai.authorizer' => DenyBlogAi::class,
    ]);

    $user = new User();
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user)
        ->post(route('blog.admin.ai.summary'), ['title' => 'Draft'])
        ->assertForbidden();
});

it('allows the host application to replace an AI action contract', function (): void {
    app()->instance(BlogSummaryGenerator::class, new class() implements BlogSummaryGenerator
    {
        public function execute(array $data): string
        {
            return 'Custom summary';
        }
    });

    expect(app(BlogSummaryGenerator::class)->execute([
        'title' => 'Custom',
        'content' => null,
        'language' => 'en',
    ]))->toBe('Custom summary');
});

function largePng(): string
{
    $image = imagecreatetruecolor(2400, 1200);
    imagefill($image, 0, 0, imagecolorallocate($image, 26, 36, 56));
    ob_start();
    imagepng($image);
    $content = ob_get_clean();

    return $content;
}

class TestAttachmentPathGenerator implements AttachmentPathGenerator
{
    public function generate(string $fileName, string $extension, ?string $directory = null): string
    {
        return 'custom/'.pathinfo($fileName, PATHINFO_FILENAME).'.'.$extension;
    }
}

class DenyBlogAi implements BlogAiAuthorizer
{
    public function authorize(Request $request): bool
    {
        return false;
    }
}
