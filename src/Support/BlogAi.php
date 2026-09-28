<?php

namespace Chuoke\Blog\Support;

use Chuoke\Blog\Exceptions\BlogAiUnavailable;

class BlogAi
{
    public static function ensureAvailable(): void
    {
        if (! config('blog.ai.enabled', false)) {
            throw new BlogAiUnavailable('Blog AI is disabled. Set blog.ai.enabled to true.');
        }

        if (! class_exists('Laravel\\Ai\\Ai')) {
            throw new BlogAiUnavailable('Blog AI requires laravel/ai and PHP 8.3 or later. Run: composer require laravel/ai');
        }
    }

    /**
     * @return array{string|null, string|null}
     */
    public static function textProviderAndModel(): array
    {
        return [
            config('blog.ai.text.provider'),
            config('blog.ai.text.model'),
        ];
    }

    /**
     * @return array{string|null, string|null}
     */
    public static function imageProviderAndModel(): array
    {
        return [
            config('blog.ai.image.provider'),
            config('blog.ai.image.model'),
        ];
    }

    public static function prompt(string $ability): ?string
    {
        $prompt = config('blog.ai.prompts.'.$ability);

        return is_string($prompt) && trim($prompt) !== '' ? trim($prompt) : null;
    }
}
