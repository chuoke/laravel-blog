<?php

namespace Chuoke\Blog\Contracts;

interface BlogSlugGenerator
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    public function execute(array $data): string;
}
