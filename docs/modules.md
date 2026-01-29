---
title: Modules
description: Creating and organising modules in Core PHP Framework applications
updated: 2026-01-29
---

# Modules

Modules are the building blocks of Core PHP Framework applications. Each module is a self-contained feature that registers itself via lifecycle events.

## Module Structure

A typical module follows this structure:

```
app/Mod/Blog/
├── Boot.php              # Entry point - event listeners
├── Models/
│   └── Post.php          # Eloquent models
├── Routes/
│   ├── web.php           # Public routes
│   └── api.php           # API routes
├── Views/
│   ├── index.blade.php
│   └── posts/
│       └── show.blade.php
├── Livewire/
│   ├── PostListPage.php
│   └── PostShowPage.php
├── Actions/
│   └── CreatePost.php    # Business logic
├── Services/
│   └── PostService.php
├── Migrations/
│   └── 2025_01_01_create_posts_table.php
└── Tests/
    ├── PostTest.php
    └── CreatePostTest.php
```

## The Boot Class

Every module requires a `Boot.php` file that declares its event listeners:

```php
<?php

declare(strict_types=1);

namespace App\Mod\Blog;

use Core\Events\WebRoutesRegistering;
use Core\Events\ApiRoutesRegistering;
use Core\Events\AdminPanelBooting;
use Core\Events\ConsoleBooting;

class Boot
{
    /**
     * Static listeners array - module is only instantiated when these events fire
     */
    public static array $listens = [
        WebRoutesRegistering::class => 'onWebRoutes',
        ApiRoutesRegistering::class => 'onApiRoutes',
        AdminPanelBooting::class => ['onAdminPanel', 10],  // With priority
        ConsoleBooting::class => 'onConsole',
    ];

    public function onWebRoutes(WebRoutesRegistering $event): void
    {
        // Register routes
        $event->routes(fn() => require __DIR__.'/Routes/web.php');

        // Register view namespace (accessed as 'blog::view.name')
        $event->views('blog', __DIR__.'/Views');
    }

    public function onApiRoutes(ApiRoutesRegistering $event): void
    {
        $event->routes(fn() => require __DIR__.'/Routes/api.php');
    }

    public function onAdminPanel(AdminPanelBooting $event): void
    {
        // Register navigation item
        $event->navigation('Blog', 'blog.admin.index', 'newspaper');

        // Register admin resources
        $event->resource('posts', PostResource::class);
    }

    public function onConsole(ConsoleBooting $event): void
    {
        // Register artisan commands
        $event->commands([
            ImportPostsCommand::class,
            PublishScheduledPostsCommand::class,
        ]);

        // Register scheduled tasks
        $event->schedule(function ($schedule) {
            $schedule->command('blog:publish-scheduled')->hourly();
        });
    }
}
```

## Lifecycle Events

### WebRoutesRegistering

Fired when web routes are being registered. Use for public-facing routes.

```php
public function onWebRoutes(WebRoutesRegistering $event): void
{
    // Register route file
    $event->routes(fn() => require __DIR__.'/Routes/web.php');

    // Register view namespace
    $event->views('blog', __DIR__.'/Views');

    // Register Blade components
    $event->components('blog', __DIR__.'/Views/Components');

    // Register middleware
    $event->middleware('blog.auth', BlogAuthMiddleware::class);
}
```

### ApiRoutesRegistering

Fired when API routes are being registered. Routes are automatically prefixed with `/api`.

```php
public function onApiRoutes(ApiRoutesRegistering $event): void
{
    $event->routes(fn() => require __DIR__.'/Routes/api.php');

    // Register API resources
    $event->resource('posts', PostApiResource::class);
}
```

### AdminPanelBooting

Fired when the admin panel is being set up (requires `core-admin` package).

```php
public function onAdminPanel(AdminPanelBooting $event): void
{
    // Navigation item with icon
    $event->navigation('Blog', 'blog.admin.index', 'newspaper');

    // Navigation group with sub-items
    $event->navigationGroup('Blog', [
        ['Posts', 'blog.admin.posts', 'file-text'],
        ['Categories', 'blog.admin.categories', 'folder'],
        ['Tags', 'blog.admin.tags', 'tag'],
    ], 'newspaper');

    // Register admin resource
    $event->resource('posts', PostResource::class);

    // Register widget for dashboard
    $event->widget(RecentPostsWidget::class);
}
```

### ClientRoutesRegistering

Fired for authenticated SaaS routes (dashboard, settings, etc.).

```php
public function onClientRoutes(ClientRoutesRegistering $event): void
{
    $event->routes(fn() => require __DIR__.'/Routes/client.php');
}
```

### ConsoleBooting

Fired when Artisan is bootstrapping.

```php
public function onConsole(ConsoleBooting $event): void
{
    // Register commands
    $event->commands([
        ImportPostsCommand::class,
    ]);

    // Register scheduled tasks
    $event->schedule(function ($schedule) {
        $schedule->command('blog:publish-scheduled')
            ->hourly()
            ->withoutOverlapping();
    });
}
```

### McpToolsRegistering

Fired when the MCP server is being set up (requires `core-mcp` package).

```php
public function onMcpTools(McpToolsRegistering $event): void
{
    $event->tool('create_post', CreatePostTool::class);
    $event->tool('list_posts', ListPostsTool::class);
}
```

## Event Priorities

You can specify a priority for event listeners. Higher numbers execute first:

```php
public static array $listens = [
    AdminPanelBooting::class => ['onAdminPanel', 100],  // High priority
    WebRoutesRegistering::class => ['onWebRoutes', 10], // Normal priority
];
```

Priorities are useful when:
- Your module needs to register before/after other modules
- You need to override routes from other modules
- You need to modify admin navigation order

## View Namespacing

Views are namespaced by the identifier you provide:

```php
$event->views('blog', __DIR__.'/Views');
```

Access views using the namespace prefix:

```blade
{{-- In controllers/components --}}
return view('blog::posts.index');

{{-- In Blade templates --}}
@include('blog::partials.sidebar')
@extends('blog::layouts.main')
```

## Route Files

### Web Routes (`Routes/web.php`)

```php
<?php

use App\Mod\Blog\Livewire\PostListPage;
use App\Mod\Blog\Livewire\PostShowPage;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', PostListPage::class)->name('index');
    Route::get('/{post:slug}', PostShowPage::class)->name('show');
});

// With middleware
Route::middleware(['auth'])->prefix('blog')->name('blog.')->group(function () {
    Route::get('/my-posts', MyPostsPage::class)->name('my-posts');
});
```

### API Routes (`Routes/api.php`)

```php
<?php

use App\Mod\Blog\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->name('api.blog.')->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

## Actions Pattern

For complex business logic, use the Actions pattern:

```php
<?php

declare(strict_types=1);

namespace App\Mod\Blog\Actions;

use App\Mod\Blog\Models\Post;
use Core\Action;
use Illuminate\Support\Str;

class CreatePost
{
    use Action;

    public function handle(array $data): Post
    {
        return Post::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'content' => $data['content'],
            'published_at' => $data['publish_now'] ? now() : null,
        ]);
    }
}
```

Usage:

```php
$post = CreatePost::run([
    'title' => 'My Post',
    'content' => 'Content here...',
    'publish_now' => true,
]);
```

## Module Discovery

Modules are discovered automatically from paths configured in `config/core.php`:

```php
'module_paths' => [
    app_path('Core'),     // Framework overrides
    app_path('Mod'),      // Application modules
    app_path('Website'),  // Website-specific modules
],
```

### Caching

In production, module discovery is cached. Clear the cache when adding new modules:

```bash
php artisan cache:clear
```

Or disable caching during development:

```env
CORE_CACHE_DISCOVERY=false
```

## Creating Modules with Artisan

The `make:mod` command scaffolds a new module:

```bash
# Full module with all features
php artisan make:mod Blog --all

# Web routes only
php artisan make:mod Blog --web

# API routes only
php artisan make:mod Blog --api

# Admin panel integration
php artisan make:mod Blog --admin

# Combination
php artisan make:mod Blog --web --api --admin
```

## Module Dependencies

If your module depends on another module, check for its presence:

```php
public function onWebRoutes(WebRoutesRegistering $event): void
{
    // Check if core-tenant is available
    if (!class_exists(\Core\Tenant\Models\Workspace::class)) {
        return;
    }

    $event->routes(fn() => require __DIR__.'/Routes/web.php');
}
```

## Best Practices

### Keep Modules Focused

Each module should represent a single feature or domain:

- `Blog` - Blog posts, categories, tags
- `Shop` - Products, orders, cart
- `Newsletter` - Subscribers, campaigns

### Use Clear Naming

- Module name: PascalCase singular (`Blog`, not `Blogs`)
- Namespace: `App\Mod\{ModuleName}`
- View namespace: lowercase (`blog::`, `shop::`)

### Isolate Dependencies

Keep inter-module dependencies minimal. If modules need to communicate:

1. Use events (preferred)
2. Use interfaces and dependency injection
3. Use shared services in `app/Services/`

### Test Modules in Isolation

Write tests that don't depend on other modules being present:

```php
it('creates a blog post', function () {
    $post = Post::create([
        'title' => 'Test',
        'slug' => 'test',
        'content' => 'Content',
    ]);

    expect($post)->toBeInstanceOf(Post::class);
    expect($post->title)->toBe('Test');
});
```
