<?php

namespace Chuoke\Blog\Http\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareBlogInertiaRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        Inertia::share('blog.routes', [
            'admin' => '/'.trim(config('blog.admin_route_prefix', 'admin/blog'), '/'),
            'api' => '/'.trim(config('blog.api_route_prefix', 'api/blog'), '/'),
        ]);

        return $next($request);
    }
}
