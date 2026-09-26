<?php

use Illuminate\Support\Facades\Route;
use Chuoke\Blog\Http\Admin\Controllers\PostController;
use Chuoke\Blog\Http\Admin\Controllers\CategoryController;
use Chuoke\Blog\Http\Admin\Controllers\TagController;
use Chuoke\Blog\Http\Admin\Controllers\DashboardController;

Route::prefix(config('blog.admin_route_prefix', 'admin/blog'))
    ->middleware(config('blog.admin_middleware', ['web', 'auth']))
    ->name('blog.admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::put('posts/{post}/pin', [PostController::class, 'togglePin'])->name('posts.pin');
        Route::post('posts/{post}/translations/{language}', [PostController::class, 'createTranslation'])->name('posts.translations.store');
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('tags', TagController::class)->except(['create', 'show', 'edit']);
    });
