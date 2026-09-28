<?php

namespace Chuoke\Blog\Contracts;

use Illuminate\Http\Request;

interface BlogAiAuthorizer
{
    public function authorize(Request $request): bool;
}
