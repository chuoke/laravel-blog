<?php

namespace Chuoke\Blog\Contracts;

interface BlogContentReviewer
{
    /**
     * @param  array{title:?string, content:?string, language:?string}  $data
     * @return array{score:int, decision:string, summary:string, strengths:array<int, string>, issues:array<int, string>}
     */
    public function execute(array $data): array;
}
