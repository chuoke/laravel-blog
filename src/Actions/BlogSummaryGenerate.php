<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Ai\BlogSummaryGenerateAgent;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Support\BlogAi;
use RuntimeException;

class BlogSummaryGenerate implements BlogSummaryGenerator
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    public function execute(array $data): string
    {
        BlogAi::ensureAvailable();
        [$provider, $model] = BlogAi::textProviderAndModel();
        $response = (new BlogSummaryGenerateAgent)->prompt($this->buildPrompt($data), provider: $provider, model: $model);
        $summary = trim((string) ($response->structured['summary'] ?? ''));

        if ($summary === '') {
            throw new RuntimeException('AI did not return a blog summary.');
        }

        return $summary;
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
