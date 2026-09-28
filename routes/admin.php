<?php

use Chuoke\Blog\Http\Admin\Controllers\AiController;
use Chuoke\Blog\Http\Admin\Controllers\CategoryController;
use Chuoke\Blog\Http\Admin\Controllers\CoverUploadController;
use Chuoke\Blog\Http\Admin\Controllers\DashboardController;
use Chuoke\Blog\Http\Admin\Controllers\PostController;
use Chuoke\Blog\Http\Admin\Controllers\TagController;
use Chuoke\Blog\Http\Admin\Middleware\AuthorizeBlogAi;
use Chuoke\Blog\Http\Admin\Middleware\ShareBlogInertiaRoutes;
use Illuminate\Support\Facades\Route;

Route::prefix(config('blog.admin_route_prefix', 'admin/blog'))
    ->middleware([...config('blog.admin_middleware', ['web', 'auth']), ShareBlogInertiaRoutes::class])
    ->name('blog.admin.')
    ->group(function () {
        $aiController = config('blog.ai.controller', AiController::class);

        Route::prefix('ai')->name('ai.')->group(function () use ($aiController) {
            Route::post('summary', [$aiController, 'summary'])
                ->middleware([AuthorizeBlogAi::class, ...config('blog.ai.middleware', [])])
                ->name('summary');
            Route::post('review', [$aiController, 'review'])
                ->middleware([AuthorizeBlogAi::class, ...config('blog.ai.middleware', [])])
                ->name('review');
            Route::post('cover', [$aiController, 'cover'])
                ->middleware([AuthorizeBlogAi::class, ...config('blog.ai.image_middleware', [])])
                ->name('cover');
        });

        Route::post('cover-upload', CoverUploadController::class)
            ->middleware(config('blog.ai.image_middleware', []))
            ->name('cover-upload');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::put('posts/{post}/pin', [PostController::class, 'togglePin'])->name('posts.pin');
        Route::post('posts/{post}/translations/{language}', [PostController::class, 'createTranslation'])->name('posts.translations.store');
        Route::post('posts/{post}/ai/translate', [$aiController, 'translate'])
            ->middleware([AuthorizeBlogAi::class, ...config('blog.ai.middleware', [])])
            ->name('posts.ai.translate');
        Route::resource('posts', PostController::class);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('tags', TagController::class)->except(['show']);
    });
