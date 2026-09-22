<?php

use Illuminate\Support\Facades\Route;
use Chuoke\Blog\Http\Api\Controllers\PostController;
use Chuoke\Blog\Http\Api\Controllers\PostPinController;
use Chuoke\Blog\Http\Api\Controllers\PostStatusController;
use Chuoke\Blog\Http\Api\Controllers\PostViewController;
use Chuoke\Blog\Http\Api\Controllers\CategoryController;
use Chuoke\Blog\Http\Api\Controllers\TagController;
use Chuoke\Blog\Http\Api\Controllers\AttachmentController;

Route::prefix(config('blog.api_route_prefix', 'api/blog'))
    ->middleware(config('blog.api_middleware', ['api']))
    ->name('blog.api.')
    ->group(function () {

        Route::apiResource('posts', PostController::class);
        Route::put('posts/{post}/pin', [PostPinController::class, 'update'])->name('posts.pin');
        Route::put('posts/{post}/status', [PostStatusController::class, 'update'])->name('posts.status');
        Route::post('posts/{post}/view', [PostViewController::class, 'store'])->name('posts.view');

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('tags', TagController::class);
        Route::apiResource('attachments', AttachmentController::class)->except(['update']);

    });
