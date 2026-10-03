<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Ai\BlogContentTranslateAgent;
use Chuoke\Blog\Contracts\BlogContentTranslator;
use Chuoke\Blog\Support\BlogAi;
use Illuminate\Support\Str;
use RuntimeException;

class BlogContentTranslate implements BlogContentTranslator
{
    /**
     * @param  array{title:string, summary:?string, content:string, sourceLanguage:string, targetLanguage:string}  $data
     * @return array{title:string, summary:string, content:string, slug:string}
     */
    public function execute(array $data): array
    {
        BlogAi::ensureAvailable();
        [$provider, $model] = BlogAi::textProviderAndModel();
        $response = (new BlogContentTranslateAgent)->prompt($this->buildPrompt($data), provider: $provider, model: $model);

        $translation = [
            'title' => trim((string) ($response->structured['title'] ?? '')),
            'summary' => trim((string) ($response->structured['summary'] ?? '')),
            'content' => trim((string) ($response->structured['content'] ?? '')),
            'slug' => Str::slug(trim((string) ($response->structured['slug'] ?? ''))),
        ];

        if ($translation['title'] === '' || $translation['content'] === '' || $translation['slug'] === '') {
            throw new RuntimeException('AI did not return a complete blog translation.');
        }

        return $translation;
    }

    /**
     * @param  array{title:string, summary:?string, content:string, sourceLanguage:string, targetLanguage:string}  $data
     */
    private function buildPrompt(array $data): string
    {
        return implode(PHP_EOL, [
            'Source language: '.$data['sourceLanguage'],
            'Target language: '.$data['targetLanguage'],
            '<article-title>'.$data['title'].'</article-title>',
            '<article-summary>'.($data['summary'] ?? '').'</article-summary>',
            '<article-content>'.$data['content'].'</article-content>',
        ]);
    }
}
