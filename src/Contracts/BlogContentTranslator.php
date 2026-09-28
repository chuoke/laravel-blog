<?php

namespace Chuoke\Blog\Contracts;

interface BlogContentTranslator
{
    /**
     * @param  array{title:string, summary:?string, content:string, sourceLanguage:string, targetLanguage:string}  $data
     * @return array{title:string, summary:string, content:string}
     */
    public function execute(array $data): array;
}
