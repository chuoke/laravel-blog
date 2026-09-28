<?php

namespace Chuoke\Blog\Providers;

use Chuoke\Blog\Actions\BlogContentReview;
use Chuoke\Blog\Actions\BlogCoverGenerate;
use Chuoke\Blog\Actions\BlogSummaryGenerate;
use Chuoke\Blog\BlogManager;
use Chuoke\Blog\Console\Commands\InstallCommand;
use Chuoke\Blog\Contracts\AttachmentPathGenerator;
use Chuoke\Blog\Contracts\BlogContentReviewer;
use Chuoke\Blog\Contracts\BlogCoverGenerator;
use Chuoke\Blog\Contracts\BlogSummaryGenerator;
use Chuoke\Blog\Support\DefaultAttachmentPathGenerator;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/blog.php', 'blog'
        );

        // BlogManager memoizes query results for the lifetime of the binding.
        // Use scoped() rather than singleton() so long-running processes
        // (e.g. Octane workers, queued jobs) get a fresh instance - and
        // therefore a fresh cache - for each request/job instead of leaking
        // stale data across them.
        $this->app->scoped(BlogManager::class, function ($app) {
            return new BlogManager;
        });

        $this->registerActions();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'blog');

        $this->shareInertiaRoutes();
        $this->configurePublishing();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
    }

    protected function shareInertiaRoutes(): void
    {
        if (! class_exists(Inertia::class)) {
            return;
        }

        Inertia::share('blog.routes', function (): array {
            return [
                'admin' => '/'.trim(config('blog.admin_route_prefix', 'admin/blog'), '/'),
                'api' => '/'.trim(config('blog.api_route_prefix', 'api/blog'), '/'),
            ];
        });
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/admin.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/front.php');
    }

    /**
     * Register the package views.
     */
    protected function registerViews(): void
    {
        $theme = config('blog.theme', 'default');
        $publishedThemes = resource_path('views/vendor/blog/themes');
        $packageThemes = __DIR__.'/../../resources/views/themes';

        // View resolution order for the 'blog' namespace (first match wins):
        // 1. The host app's published/customized active theme
        // 2. The package's built-in active theme
        // 3. The host app's published/customized default theme (partial overrides)
        // 4. The package's built-in default theme (final fallback)
        $paths = array_unique(array_filter([
            $publishedThemes.'/'.$theme,
            $packageThemes.'/'.$theme,
            $publishedThemes.'/default',
            $packageThemes.'/default',
        ]));

        foreach ($paths as $path) {
            $this->loadViewsFrom($path, 'blog');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $packageThemes => $publishedThemes,
            ], 'blog-views');
        }
    }

    /**
     * Register the action bindings.
     */
    protected function registerActions(): void
    {
        $this->app->bind(AttachmentPathGenerator::class, fn () => app(config('blog.attachment.path_generator', DefaultAttachmentPathGenerator::class)));
        $this->app->bind(BlogSummaryGenerator::class, BlogSummaryGenerate::class);
        $this->app->bind(BlogContentReviewer::class, BlogContentReview::class);
        $this->app->bind(BlogCoverGenerator::class, BlogCoverGenerate::class);
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/blog.php' => config_path('blog.php'),
            ], 'blog-config');

            $this->publishes([
                __DIR__.'/../../lang' => $this->app->langPath('vendor/blog'),
            ], 'blog-lang');

            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'blog-migrations');

            $this->publishes([
                __DIR__.'/../../resources/js/Pages/Blog' => resource_path('js/Pages/Blog'),
                __DIR__.'/../../resources/js/i18n' => resource_path('js/Pages/Blog/i18n'),
                __DIR__.'/../../resources/css/blog' => resource_path('css/blog'),
            ], 'blog-assets');
        }
    }

    /**
     * Register the package migrations.
     */
    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }
}
