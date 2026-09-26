<?php

use Chuoke\Blog\Http\Front\Controllers\CategoryController;
use Chuoke\Blog\Http\Front\Controllers\HomeController;
use Chuoke\Blog\Http\Front\Controllers\PostController;
use Chuoke\Blog\Http\Front\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('blog.front_route_prefix', 'blog'))
    ->middleware(config('blog.front_middleware', ['web']))
    ->name('blog.')
    ->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');
        Route::get('/tag/{slug}', [TagController::class, 'show'])->name('tag.show');
        Route::get('/{post}', [PostController::class, 'show'])
            ->where('post', '[A-Za-z0-9]+-.+')
            ->name('posts.show');
    });
