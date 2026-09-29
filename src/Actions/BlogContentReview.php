<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Ai\BlogContentReviewAgent;
use Chuoke\Blog\Contracts\BlogContentReviewer;
use Chuoke\Blog\Support\BlogAi;

class BlogContentReview implements BlogContentReviewer
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     * @return array{score:int, decision:string, summary:string, strengths:array<int, string>, issues:array<int, string>}
     */
    public function execute(array $data): array
    {
        BlogAi::ensureAvailable();
        [$provider, $model] = BlogAi::textProviderAndModel();
        $response = (new BlogContentReviewAgent)->prompt($this->buildPrompt($data), provider: $provider, model: $model);

        return [
            'score' => (int) ($response->structured['score'] ?? 0),
            'decision' => (string) ($response->structured['decision'] ?? 'needs_revision'),
            'summary' => trim((string) ($response->structured['summary'] ?? '')),
            'strengths' => $this->lines($response->structured['strengths'] ?? []),
            'issues' => $this->lines($response->structured['issues'] ?? []),
        ];
    }

    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    private function buildPrompt(array $data): string
    {
        return implode(PHP_EOL, [
            'Response language: '.app()->getLocale(),
            'Article language: '.($data['language'] ?: 'unspecified'),
            '<article-title>'.($data['title'] ?: 'Untitled').'</article-title>',
            '<article-content>'.($data['content'] ?: 'No content provided').'</article-content>',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function lines(mixed $lines): array
    {
        return collect($lines)
            ->filter(fn ($line): bool => is_string($line) && trim($line) !== '')
            ->map(fn (string $line): string => trim($line))
            ->values()
            ->all();
    }
}
