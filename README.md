# Laravel Blog

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chuoke/laravel-blog.svg?style=flat-square)](https://packagist.org/packages/chuoke/laravel-blog)

A standalone, themeable blog package for Laravel. Ships with an Inertia/Vue admin panel, a `Blog` facade for on-demand data access, and four ready-to-use front-end themes — all fully driven by DaisyUI color tokens.

## Features

- **Markdown-based** content with full CommonMark support
- **Multi-language** posts, categories & tags (JSON-based translations)
- **Admin Panel** — Inertia.js + Vue 3 dashboard with post/category/tag CRUD
- **Theme System** — 4 built-in themes, easily extensible
- **DaisyUI Color Tokens** — 30+ color themes, dark mode included, zero custom CSS needed
- **Blog Facade** — pull-based data API for maximum theme flexibility
- **SEO Ready** — auto-generated title, description, Open Graph, canonical URLs
- **Embeddable** — drop into any existing Laravel project with one config line

## Requirements

- PHP 8.3+
- GD with WebP support (for cover upload and generation)
- Laravel 12 or 13

## Installation

```bash
composer require chuoke/laravel-blog
```

The package auto-discovers its service provider. Then run:

```bash
php artisan blog:install
```

This publishes config, migrations, and admin assets. Then migrate:

```bash
php artisan migrate
```

The admin panel expects your host application to already have Inertia.js 3, Vue 3, Tailwind CSS, and DaisyUI configured. Install the editor dependency:

```bash
npm install md-editor-v3 vue-i18n
```

Register the published blog messages in the host application's Inertia entry:

```ts
import { createI18n } from 'vue-i18n';
import { blogAdminMessages } from './Pages/Blog/i18n/admin';

const locale = document.documentElement.lang === 'zh-CN' ? 'zh-CN' : 'en';
const i18n = createI18n({ legacy: false, locale, fallbackLocale: 'en', messages: blogAdminMessages });

createInertiaApp({
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) }).use(plugin).use(i18n).mount(el);
    },
});
```

### Admin Layout and Page Overrides

The package admin pages use the package layout by default. Keep that layout when the host does not need a custom page.

To override selected pages, resolve a host page first and fall back to the package source. A host page at the same path replaces only that page. For example, `resources/js/admin-blog/pages/Blog/Admin/Posts/Edit.vue` replaces the package edit screen while every other page still comes from the package.

```ts
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';

const localPages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
const packagePages = import.meta.glob<DefineComponent>(
    '../../../vendor/chuoke/laravel-blog/resources/js/Pages/**/*.vue',
);

createInertiaApp({
    resolve: async (name) => {
        const localPath = `./pages/${name}.vue`;
        const packagePath = `../../../vendor/chuoke/laravel-blog/resources/js/Pages/${name}.vue`;

        return await resolvePageComponent(
            localPages[localPath] ? localPath : packagePath,
            { ...localPages, ...packagePages },
        );
    },
    // setup omitted
});
```

To replace the layout for every resolved page, import the host layout and assign it before returning the resolved page. This also replaces layouts declared by package pages.

```ts
import HostAdminLayout from './layouts/HostAdminLayout.vue';

const page = await resolvePageComponent(path, pages);
page.default.layout = HostAdminLayout;

return page;
```

### Admin Styles

The admin panel uses Tailwind and DaisyUI utility classes. The package deliberately does not register a CSS entry in the host application, so add one to the host build and let Tailwind scan both the host overrides and the package pages:

```css
/* resources/css/admin-blog.css */
@import 'tailwindcss' source(none);

@plugin "daisyui";

@source '../js/admin-blog';
@source '../../vendor/chuoke/laravel-blog/resources/js';
@source '../views/admin-blog.blade.php';
```

Load that entry from the admin Blade view, for example `@vite('resources/js/admin-blog/app.ts')` after importing the stylesheet in `app.ts`. Adjust the relative paths for your project. If you publish `blog-assets`, scan `resources/js/Pages/Blog` instead of the package path. Missing either source path can cause Tailwind to omit classes used by the corresponding pages.

### Publishing Individually

```bash
# Config only
php artisan vendor:publish --tag=blog-config

# Migrations only
php artisan vendor:publish --tag=blog-migrations

# Admin Vue, i18n, and compiled theme CSS assets
php artisan vendor:publish --tag=blog-assets

# Front-end theme views (for customization)
php artisan vendor:publish --tag=blog-views
```

## Configuration

All options live in `config/blog.php`:

```php
return [
    // The Eloquent model that represents blog authors
    'author_model' => App\Models\User::class,

    // Default locale & supported locales
    'locale' => 'en',
    'supported_locales' => ['en' => 'English', 'zh' => 'Chinese'],

    // Front-end view theme: 'default', 'minimal', 'magazine', or 'newsroom'
    'theme' => 'default',

    // DaisyUI color theme: 'light', 'dark', 'cupcake', 'nord', 'business', etc.
    'color_theme' => 'light',

    // Set to your app's layout to embed into an existing project
    // e.g. 'layouts.app'. When null, the theme's own layout is used.
    'layout' => null,

    // Route prefixes & middleware
    'front_route_prefix' => 'blog',
    'front_middleware'   => ['web'],
    'admin_route_prefix' => 'admin/blog',
    'admin_middleware'    => ['web', 'auth'],
    'api_route_prefix'   => 'api/blog',
    'api_middleware'      => ['web', 'auth'],
];
```

> **Security note:** API write routes use the host web session and require an authenticated user by default. Add your application's authorization middleware when only designated users should administer the blog.
>
> ⚠️ **Markdown note:** `config('blog.markdown.html_input')` defaults to `'strip'`, which removes raw HTML embedded in post Markdown. Only set it to `'allow'` if every author who can create/edit posts is fully trusted — post content is rendered unescaped (`{!! !!}`) in the front-end views, so allowing raw HTML from untrusted authors (especially combined with an unprotected API) is a stored-XSS risk.

## Themes

Four built-in themes with distinct design philosophies:

| Theme | Style | Layout | Best For |
|---|---|---|---|
| **default** | Modern card-based, Inter font | Main + sidebar | General blogs, tech blogs |
| **minimal** | Typography-focused, Newsreader serif | Single-column centered | Personal essays, writing |
| **magazine** | News/editorial, Outfit + Source Serif | Full-width hero grid + multi-column | News sites, online magazines |
| **newsroom** | Laravel News-inspired editorial | Structured news feed + feature sections | Developer news, publications |

### Switching Themes

```php
// config/blog.php
'theme' => 'magazine',
```

### Switching Colors

Every theme uses DaisyUI semantic tokens (`text-primary`, `bg-base-100`, etc.), so changing the color palette is one line:

```php
// config/blog.php
'color_theme' => 'nord',    // Muted Nordic palette
'color_theme' => 'dark',    // Dark mode
'color_theme' => 'cupcake', // Soft pastel
```

See all available themes: [daisyui.com/docs/themes](https://daisyui.com/docs/themes/)

### Theme Style Loading

The bundled default theme loads its precompiled Tailwind and DaisyUI stylesheet from the package layout, so it works without adding a Vite entry or publishing assets. This keeps the public theme independent from the host application's CSS bundle.

When using a host layout (`'layout' => 'layouts.app'`) or replacing a theme layout, the host owns style loading. Include the host CSS entry with `@vite(...)` and make its Tailwind entry scan the blog templates. For package templates:

```css
@source '../../vendor/chuoke/laravel-blog/resources/views/themes/**/*.blade.php';
```

For published templates, scan `resources/views/vendor/blog/themes/**/*.blade.php` instead. Include any fonts and theme-specific CSS required by the layout you replace. This is also the right setup when creating a custom theme.

### Creating a Custom Theme

1. Publish views: `php artisan vendor:publish --tag=blog-views`
2. Create a new directory: `resources/views/vendor/blog/themes/my-theme/`
3. Copy any files from `default/` as a starting point
4. Set `'theme' => 'my-theme'` in config
5. Any missing views will automatically fall back to the `default` theme

### Embedding into an Existing Project

If your project already has a layout with navigation and footer:

```php
// config/blog.php
'layout' => 'layouts.app',
```

Your layout just needs `@yield('content')` and `@yield('title')`. The blog's content sections will slot right in. Views provide a bare `@section('title')` (no app-name suffix) so your layout controls the final `<title>` composition — the bundled theme layouts render it as `Title — App Name`. Optional sections: `meta_description`, `canonical`, `og_type`, `og_image`.

## Blog Facade API

The `Blog` facade is the primary data interface. Themes call it directly in Blade templates — controllers stay thin.

### Posts

```php
Blog::latestPosts(int $limit = 10, ?string $language = null): Collection
Blog::paginatedPosts(array $filters = [], int $perPage = 15, ?string $language = null): LengthAwarePaginator
Blog::pinnedPosts(int $limit = 5, ?string $language = null): Collection
Blog::popularPosts(int $limit = 5, ?string $language = null): Collection
Blog::post(string $uid, ?string $language = null): ?Post
Blog::relatedPosts(Post $post, int $limit = 5): Collection
Blog::previousPost(Post $post): ?Post
Blog::nextPost(Post $post): ?Post
Blog::recordView(Post $post): void
Blog::forgetFrontCache(): void
```

**Filters for `paginatedPosts`:** `search`, `category_id`, `tag_id`, `author_id`

### Categories & Tags

```php
Blog::categories(): Collection
Blog::categoriesWithCount(?string $language = null): Collection
Blog::category(string $slug): ?Category

Blog::tags(): Collection
Blog::tagsWithCount(?string $language = null): Collection
Blog::tag(string $slug): ?Tag
```

### Aggregation

```php
Blog::archives(?string $language = null): Collection  // [{year, month, count}]
```

### Usage in Blade

```blade
{{-- Themes pull exactly the data they need --}}
@php
    $posts = Blog::latestPosts(6);
    $categories = Blog::categoriesWithCount();
@endphp

@foreach($posts as $post)
    <h2>{{ $post->title }}</h2>
@endforeach
```

### Front-End Cache

The latest, pinned, popular, category, tag, and archive aggregates are cached across requests for five minutes by default. Their expiry is randomly staggered by up to 10% to avoid simultaneous cache rebuilds.

Post, category, and tag changes invalidate these aggregates immediately. View counts are written immediately; the popular-post order can take up to the cache TTL to refresh.

```php
// config/blog.php
'cache' => [
    'front_ttl' => 300, // seconds; set to 0 to disable the added expiry jitter
],
```

Call `Blog::forgetFrontCache()` only when changing blog data outside of the package actions.

## Front-End Routes

| URL | Name | Page |
|---|---|---|
| `/blog` | `blog.home` | Homepage |
| `/blog/posts` | `blog.posts.index` | Post listing |
| `/blog/{uid}-{slug}` | `blog.posts.show` | Post detail |
| `/blog/category/{slug}` | `blog.category.show` | Category page |
| `/blog/tag/{slug}` | `blog.tag.show` | Tag page |

## Admin Panel

Access at `/admin/blog` (configurable). Built with Inertia.js + Vue 3. Features:

- **Dashboard** — stats overview, trending posts, recent activity
- **Posts** — CRUD with Markdown editor, cover image, pinning, scheduling
- **Categories** — sortable, multi-language names & descriptions
- **Tags** — multi-language names

Install admin assets:

```bash
php artisan blog:install
```

The admin routes use `blog.admin_middleware`; by default this is `['web', 'auth']`, so new posts are assigned to the current authenticated user.

### AI Capabilities

AI is off by default. Enable it only after installing and configuring [`laravel/ai`](https://github.com/laravel/ai) and its provider credentials:

```bash
composer require laravel/ai
```

```php
// config/blog.php
'ai' => [
    'enabled' => true,
    'text' => ['provider' => 'openai', 'model' => 'gpt-5-mini'],
    'image' => ['provider' => 'openai', 'model' => 'gpt-image-1'],
],
```

The AI routes are unavailable while `enabled` is `false`. They still inherit `blog.admin_middleware`; applications that need a separate capability check can set `ai.authorizer` to a class implementing `Chuoke\Blog\Contracts\BlogAiAuthorizer`. Return `false` to deny a request.

```php
use Chuoke\Blog\Contracts\BlogAiAuthorizer;
use Illuminate\Http\Request;

class AuthorizeBlogAi implements BlogAiAuthorizer
{
    public function authorize(Request $request): bool
    {
        return $request->user()->can('use-blog-ai');
    }
}
```

```php
'ai' => [
    'enabled' => true,
    'authorizer' => App\Blog\AuthorizeBlogAi::class,
],
```

### Attachment Paths

`attachment.path_generator` accepts a class implementing `Chuoke\Blog\Contracts\AttachmentPathGenerator`. It receives the original name, extension, and optional directory, and must return a unique path relative to the configured disk. The default uses UUIDs; a NanoID-and-date strategy is a good host-specific alternative.

### Upgrading Published Admin Assets

Published Vue files are intentionally never overwritten by `blog:install` unless `--force` is supplied. Do not use `--force` to upgrade a customized admin panel.

For the upgrade-friendly setup, keep only intentional page overrides in a host directory and resolve every other page from `vendor/chuoke/laravel-blog/resources/js/Pages`, as shown in [Admin Layout and Page Overrides](#admin-layout-and-page-overrides). Existing published files can remain in place until their customizations have been moved; they are ignored once the resolver points to the package source.

For package updates, publish only new migrations, review the package `config/blog.php` for new keys, then run migrations:

```bash
php artisan vendor:publish --tag=blog-migrations
php artisan migrate
```

The AI cover endpoint is asynchronous. Ensure a queue worker consumes the `default` queue with a timeout of at least 300 seconds and a `retry_after` greater than that timeout. Custom admin pages must submit to `POST ai/cover`, then poll `GET ai/cover/{id}` until the status is `completed` or `failed`. A custom `ai.controller` must implement `cover` and `coverStatus`.

## Database

The package creates 6 tables via migrations:

- `blog_posts` — articles with Markdown content, status, scheduling
- `blog_categories` — hierarchical categories with JSON translations
- `blog_tags` — tags with JSON translations
- `blog_post_tag` — pivot table
- `blog_attachments` — file uploads (cover images, etc.)
- `blog_cover_generations` — short-lived AI cover-generation tasks

`author_id` is `string(36)` to support UUID, ULID, and traditional auto-increment IDs.

## Seeding

```php
// database/seeders/DatabaseSeeder.php
$this->call(\Chuoke\Blog\Database\Seeders\BlogDatabaseSeeder::class);
```

## License

MIT
