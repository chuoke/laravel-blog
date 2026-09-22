<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\TagCreateData;
use Chuoke\Blog\Models\Tag;
use Illuminate\Support\Str;

class TagCreate
{
    public function execute(TagCreateData $data): Tag
    {
        $slugName = $data->name[config('blog.locale', 'en')] ?? array_values($data->name)[0] ?? 'tag';

        return Tag::create([
            'name' => $data->name,
            'slug' => Str::slug($slugName) . '-' . strtolower(Str::random(4)),
        ]);
    }
}
