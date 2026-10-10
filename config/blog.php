<?php

use Chuoke\Blog\Actions\BlogCoverGenerate;
use Chuoke\Blog\Http\Admin\Controllers\AiController;
use Chuoke\Blog\Support\DefaultAttachmentPathGenerator;
use Chuoke\Blog\Support\Nanoid;

return [
    /*
    |--------------------------------------------------------------------------
    | Author Model
    |--------------------------------------------------------------------------
    |
    | Define the model that represents the author of a blog post.
    | Typically this is App\Models\User::class.
    |
    */
    'author_model' => 'App\Models\User',

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Define the default locale for blog content and the list of supported
    | locales for multi-language features.
    |
    */
    'locale' => 'en',

    'supported_locales' => [
        'en' => 'English',
        'zh' => 'Chinese',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | The active front-end theme. Each theme is a directory under
    | resources/views/themes/{theme}. Ships with 'default', 'minimal',
    | 'magazine', and 'newsroom'.
    |
    */
    'theme' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Color Theme (DaisyUI)
    |--------------------------------------------------------------------------
    |
    | The DaisyUI color theme applied to the blog front-end. 'editorial' is
    | this package's own default theme (warm neutral paper tones with an
    | indigo/amber accent pair). Any built-in DaisyUI theme name also works:
    | 'light', 'dark', 'cupcake', 'nord', 'business', etc.
    | See https://daisyui.com/docs/themes/
    | Ignored when using a custom layout (blog.layout).
    |
    */
    'color_theme' => 'editorial',

    /*
    |
    | The Blade layout that blog views should extend. When null, the theme's
    | own layout.blade.php is used. Set to your app's layout (e.g.
    | 'layouts.app') to embed the blog into an existing project seamlessly.
    | Your layout must provide @yield('content'), @yield('title'), and
    | optionally @stack('styles') / @stack('scripts').
    |
    */
    'layout' => null,

    /*
    |--------------------------------------------------------------------------
    | SEO
    |--------------------------------------------------------------------------
    |
    | Default metadata for blog aggregation pages. Use locale keys when the
    | site has more than one language, for example ['en' => '...', 'zh' => '...'].
    |
    */
    'seo' => [
        'title_separator' => ' — ',
        'description' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown
    |--------------------------------------------------------------------------
    |
    | 'html_input' controls how raw HTML embedded in post Markdown is handled:
    | - 'strip'  (default, recommended): raw HTML tags are removed. Safe even
    |             if post content can come from untrusted or semi-trusted authors.
    | - 'escape': raw HTML is displayed as literal text instead of being rendered.
    | - 'allow':  raw HTML is rendered as-is. Only use this if every author who
    |             can create/edit posts is fully trusted, since post content is
    |             rendered unescaped in the front-end views. Combined with an
    |             API route that isn't protected by authentication, 'allow' can
    |             lead to stored XSS.
    |
    */
    'markdown' => [
        'html_input' => 'strip',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Post UID Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for generating the external ID (uid) for blog posts.
    |
    */
    'uid' => [
        'generator' => [
            'class' => Nanoid::class,
            'config' => [
                'length' => 10,
                'alphabet' => '0123456789abcdefghijklmnopqrstuvwxyz',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the package's Inertia admin routes.
    |
    */
    'admin_route_prefix' => 'admin/blog',

    'admin_middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | API Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the package's API routes.
    |
    | The default uses the host application's web session and requires an
    | authenticated user. Add your own authorization middleware if your
    | application distinguishes blog administrators from other users.
    |
    */
    'api_route_prefix' => 'api/blog',

    'api_middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Front Routes Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the package's public-facing blog routes.
    |
    */
    'front_route_prefix' => 'blog',

    'front_middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Front-End Cache
    |--------------------------------------------------------------------------
    |
    | Cache front-end aggregates for this many seconds. Their expiry receives
    | a small random delay to prevent many keys rebuilding simultaneously.
    |
    */
    'cache' => [
        'front_ttl' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | Attachment Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for file uploads and attachment storage.
    |
    */
    'attachment' => [
        'disk' => 'public',
        'directory' => 'blog/attachments',
        'path_generator' => DefaultAttachmentPathGenerator::class,
        'max_size' => 10240, // KB

        // Only these file extensions may be uploaded. Keep this list to
        // formats you actually need; anything not listed here is rejected.
        'allowed_extensions' => [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'mp4', 'webm', 'mov',
            'mp3', 'wav', 'ogg',
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip',
        ],
    ],

    'ai' => [
        // Requires laravel/ai (and PHP 8.3 or later) when enabled.
        'enabled' => false,
        'authorizer' => null,
        'middleware' => ['throttle:10,1'],
        'image_middleware' => ['throttle:3,1'],
        'status_middleware' => ['throttle:30,1'],
        'text' => [
            'provider' => null,
            'model' => null,
        ],
        'image' => [
            'provider' => null,
            'model' => null,
        ],
        'cover' => [
            'generator' => BlogCoverGenerate::class,
            'timeout' => 3300,
            'retry_after' => 3600,
            'tries' => 2,
        ],
        'prompts' => [
            'summary' => null,
            'slug' => null,
            'review' => null,
            'cover' => null,
            'translation' => null,
        ],
        'controller' => AiController::class,
    ],
];
