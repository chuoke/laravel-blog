<?php

namespace Chuoke\Blog\Http\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BlogAiContentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:100000'],
            'language' => ['nullable', 'string', 'max:10', Rule::in(array_keys(config('blog.supported_locales', ['en' => 'English'])))],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('title') && ! $this->filled('content')) {
                    $validator->errors()->add('content', 'Provide an article title or content.');
                }
            },
        ];
    }

    /**
     * @return array{title:?string, content:?string, language:?string}
     */
    public function contentData(): array
    {
        return [
            'title' => $this->filled('title') ? $this->string('title')->toString() : null,
            'content' => $this->filled('content') ? $this->string('content')->toString() : null,
            'language' => $this->filled('language') ? $this->string('language')->toString() : null,
        ];
    }
}
