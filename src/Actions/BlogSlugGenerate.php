<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Ai\BlogSlugGenerateAgent;
use Chuoke\Blog\Contracts\BlogSlugGenerator;
use Chuoke\Blog\Support\BlogAi;
use Illuminate\Support\Str;
use RuntimeException;

class BlogSlugGenerate implements BlogSlugGenerator
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    public function execute(array $data): string
    {
        BlogAi::ensureAvailable();
        [$provider, $model] = BlogAi::textProviderAndModel();
        $response = (new BlogSlugGenerateAgent())->prompt($this->buildPrompt($data), provider: $provider, model: $model);
        $slug = Str::slug(trim((string) ($response->structured['slug'] ?? '')));

        if ($slug === '') {
            throw new RuntimeException('AI did not return a valid blog slug.');
        }

        return $slug;
    }

    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    private function buildPrompt(array $data): string
    {
        return implode(PHP_EOL, [
            'Article language: '.($data['language'] ?: 'unspecified'),
            '<article-title>'.($data['title'] ?: 'Untitled').'</article-title>',
            '<article-content>'.($data['content'] ?: 'No content provided').'</article-content>',
        ]);
    }
}
