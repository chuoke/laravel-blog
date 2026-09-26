# Laravel Blog

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

- PHP 8.2+
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

### Publishing Individually

```bash
# Config only
php artisan vendor:publish --tag=blog-config

# Migrations only
php artisan vendor:publish --tag=blog-migrations

# Admin Vue/CSS assets only
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

## Database

The package creates 5 tables via migrations:

- `blog_posts` — articles with Markdown content, status, scheduling
- `blog_categories` — hierarchical categories with JSON translations
- `blog_tags` — tags with JSON translations
- `blog_post_tag` — pivot table
- `blog_attachments` — file uploads (cover images, etc.)

`author_id` is `string(36)` to support UUID, ULID, and traditional auto-increment IDs.

## Seeding

```php
// database/seeders/DatabaseSeeder.php
$this->call(\Chuoke\Blog\Database\Seeders\BlogDatabaseSeeder::class);
```

## License

MIT
