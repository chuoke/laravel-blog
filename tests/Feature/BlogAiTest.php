<?php

use Chuoke\Blog\Actions\AttachmentUpload;
use Chuoke\Blog\Actions\BlogContentReview;
use Chuoke\Blog\Actions\BlogContentTranslate;
use Chuoke\Blog\Actions\BlogCoverAttachmentStore;
use Chuoke\Blog\Actions\BlogCoverGenerate;
use Chuoke\Blog\Actions\BlogSummaryGenerate;
use Chuoke\Blog\Ai\BlogContentReviewAgent;
use Chuoke\Blog\Ai\BlogContentTranslateAgent;
use Chuoke\Blog\Ai\BlogSummaryGenerateAgent;
use Chuoke\Blog\Contracts\AttachmentPathGenerator;
use Chuoke\Blog\Contracts\BlogAiAuthorizer;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Actions\PostCreate;
use Chuoke\Blog\Dtos\PostCreateData;
use Chuoke\Blog\Exceptions\BlogAiUnavailable;
use Chuoke\Blog\Models\Attachment;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

it('generates a summary using the configured text provider and model', function (): void {
    config(['blog.ai.prompts.summary' => 'Prefer a practical, calm editorial tone.']);
    BlogSummaryGenerateAgent::fake([['summary' => 'A practical guide to building a focused editorial workflow.']]);

    $summary = (new BlogSummaryGenerate)->execute([
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

    expect((new BlogSummaryGenerateAgent)->instructions())
        ->toContain('Prefer a practical, calm editorial tone.');
});

it('returns a structured editorial review', function (): void {
    BlogContentReviewAgent::fake([[
        'score' => 82,
        'decision' => 'needs_revision',
        'summary' => 'The structure is clear, but the conclusion needs supporting evidence.',
        'strengths' => ['Clear topic'],
        'issues' => ['Add sources for the central claim'],
    ]]);

    $review = (new BlogContentReview)->execute([
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
});

it('translates an article with the configured text provider and model', function (): void {
    config(['blog.ai.prompts.translation' => 'Keep product names in English.']);
    BlogContentTranslateAgent::fake([[
        'title' => 'Translated title',
        'summary' => 'Translated summary',
        'content' => '# Translated content',
    ]]);

    $translation = (new BlogContentTranslate)->execute([
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
    ]);

    BlogContentTranslateAgent::assertPrompted(
        fn ($prompt): bool => str_contains($prompt->prompt, 'Source language: en')
            && str_contains($prompt->prompt, 'Target language: zh_CN')
            && $prompt->provider instanceof OpenAiProvider
            && $prompt->model === 'gpt-5-mini',
    );

    expect((new BlogContentTranslateAgent)->instructions())
        ->toContain('Keep product names in English.');
});

it('translates from the original article for a translation draft', function (): void {
    config(['blog.author_model' => User::class]);
    $original = (new PostCreate)->execute(new PostCreateData(
        title: 'Original title',
        summary: 'Original summary',
        content: '# Original content',
        authorId: 1,
        language: 'en',
    ));
    $translation = (new PostCreate)->execute(new PostCreateData(
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
    ]]);

    $user = new User;
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
            && $prompt->contains('Use a warm editorial color palette.'),
    );
});

it('provides a longer default timeout for AI cover generation', function (): void {
    expect(config('blog.ai.image.timeout'))->toBe(180);
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
    app()->instance(AttachmentPathGenerator::class, new class implements AttachmentPathGenerator
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

    (new BlogSummaryGenerate)->execute([
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

    $user = new User;
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

    $user = new User;
    $user->forceFill(['id' => 1])->exists = true;

    $this->actingAs($user)
        ->post(route('blog.admin.ai.summary'), ['title' => 'Draft'])
        ->assertForbidden();
});

it('allows the host application to replace an AI action contract', function (): void {
    app()->instance(BlogSummaryGenerator::class, new class implements BlogSummaryGenerator
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
    imagedestroy($image);

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
