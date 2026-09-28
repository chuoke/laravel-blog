<?php

namespace Chuoke\Blog\Contracts;

use Chuoke\Blog\Models\Attachment;

interface BlogCoverGenerator
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     */
    public function execute(array $data): Attachment;
}
