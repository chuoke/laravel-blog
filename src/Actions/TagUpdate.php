<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Dtos\TagUpdateData;
use Chuoke\Blog\Models\Tag;

class TagUpdate
{
    public function execute(Tag $tag, TagUpdateData $data): Tag
    {
        $updateAttributes = array_filter([
            'name' => $data->name,
        ], fn($value) => $value !== null);

        if (!empty($updateAttributes)) {
            $tag->update($updateAttributes);
        }

        return $tag->fresh();
    }
}
