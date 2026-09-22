<?php

namespace Chuoke\Blog\Http\Admin\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Chuoke\Blog\Models\Post;
use Chuoke\Blog\Models\Category;
use Chuoke\Blog\Models\Tag;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'total_views' => Post::sum('view_count') ?? 0,
            'total_categories' => Category::count(),
            'total_tags' => Tag::count(),
        ];

        $recentPosts = Post::with(['category', 'author'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $topPosts = Post::with(['category', 'author'])
            ->where('status', 'published')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Blog/Admin/Dashboard/Index', [
            'stats' => $stats,
            'recentPosts' => $recentPosts,
            'topPosts' => $topPosts,
        ]);
    }
}
