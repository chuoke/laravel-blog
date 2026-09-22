# Laravel Blog

A standalone, themeable blog package for Laravel. Ships with an Inertia/Vue admin panel, a `Blog` facade for on-demand data access, and three ready-to-use front-end themes — all fully driven by DaisyUI color tokens.

## Features

- **Markdown-based** content with full CommonMark support
- **Multi-language** posts, categories & tags (JSON-based translations)
- **Admin Panel** — Inertia.js + Vue 3 dashboard with post/category/tag CRUD
- **Theme System** — 3 built-in themes, easily extensible
- **DaisyUI Color Tokens** — 30+ color themes, dark mode included, zero custom CSS needed
- **Blog Facade** — pull-based data API for maximum theme flexibility
- **SEO Ready** — auto-generated title, description, Open Graph, canonical URLs
- **Embeddable** — drop into any existing Laravel project with one config line

## Requirements

- PHP 8.2+
- Laravel 11, 12, or 13

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
npm install md-editor-v3
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

    // Front-end view theme: 'default', 'minimal', or 'magazine'
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
    'api_middleware'      => ['api'],
];
```

> ⚠️ **Security note:** the default `api_middleware` (`['api']`) only puts the routes in the API middleware group — it does **not** authenticate or authorize requests. The API exposes full create/update/delete for posts, categories, tags, and attachments. Before exposing these routes, add an auth guard (e.g. `'api_middleware' => ['api', 'auth:sanctum']`) and any authorization checks your app needs, or anyone can create/modify/delete blog content and upload files anonymously.
>
> ⚠️ **Markdown note:** `config('blog.markdown.html_input')` defaults to `'strip'`, which removes raw HTML embedded in post Markdown. Only set it to `'allow'` if every author who can create/edit posts is fully trusted — post content is rendered unescaped (`{!! !!}`) in the front-end views, so allowing raw HTML from untrusted authors (especially combined with an unprotected API) is a stored-XSS risk.

## Themes

Three built-in themes with distinct design philosophies:

| Theme | Style | Layout | Best For |
|---|---|---|---|
| **default** | Modern card-based, Inter font | Main + sidebar | General blogs, tech blogs |
| **minimal** | Typography-focused, Newsreader serif | Single-column centered | Personal essays, writing |
| **magazine** | News/editorial, Outfit + Source Serif | Full-width hero grid + multi-column | News sites, online magazines |

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

Your layout just needs `@yield('content')` and `@yield('title')`. The blog's content sections will slot right in.

## Blog Facade API

The `Blog` facade is the primary data interface. Themes call it directly in Blade templates — controllers stay thin.

### Posts

```php
Blog::latestPosts(int $limit = 10, ?string $language = null): Collection
Blog::paginatedPosts(array $filters = [], int $perPage = 15, ?string $language = null): LengthAwarePaginator
Blog::pinnedPosts(int $limit = 5, ?string $language = null): Collection
Blog::popularPosts(int $limit = 5, ?string $language = null): Collection
Blog::post(string $slug, ?string $language = null): ?Post
Blog::relatedPosts(Post $post, int $limit = 5): Collection
Blog::previousPost(Post $post): ?Post
Blog::nextPost(Post $post): ?Post
Blog::recordView(Post $post): void
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

> Sidebar data (`categoriesWithCount`, `tagsWithCount`, `archives`) is automatically cached per-request to prevent duplicate queries.

## Front-End Routes

| URL | Name | Page |
|---|---|---|
| `/blog` | `blog.home` | Homepage |
| `/blog/posts` | `blog.posts.index` | Post listing |
| `/blog/{slug}` | `blog.posts.show` | Post detail |
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
