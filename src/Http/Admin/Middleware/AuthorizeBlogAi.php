<?php

namespace Chuoke\Blog\Http\Admin\Middleware;

use Chuoke\Blog\Contracts\BlogAiAuthorizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeBlogAi
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('blog.ai.enabled', false), 404);

        $authorizer = config('blog.ai.authorizer');

        if ($authorizer !== null) {
            $authorizer = app($authorizer);

            abort_unless($authorizer instanceof BlogAiAuthorizer && $authorizer->authorize($request), 403);
        }

        return $next($request);
    }
}
