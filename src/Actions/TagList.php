<?php

namespace Chuoke\Blog\Actions;

use Chuoke\Blog\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagList
{
    public function execute(): Collection
    {
        return Tag::all();
    }
}
