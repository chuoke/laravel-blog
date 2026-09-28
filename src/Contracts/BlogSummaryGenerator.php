<?php

namespace Chuoke\Blog\Contracts;

interface BlogSummaryGenerator
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    public function execute(array $data): string;
}
