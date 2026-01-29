---
title: Architecture
description: Technical architecture of core-template - the starter template for Core PHP Framework applications
updated: 2026-01-29
---

# Architecture

core-template is the official starter template for building applications with the Core PHP Framework. It provides a pre-configured Laravel 12 application with the modular monolith architecture, Livewire 3, and Flux UI integration.

## Overview

```
core-template/
├── app/
│   ├── Http/Controllers/      # Traditional controllers (rarely used)
│   ├── Models/                # Application-wide Eloquent models
│   ├── Mod/                   # Feature modules (your code goes here)
│   └── Providers/             # Service providers
├── bootstrap/
│   ├── app.php                # Application bootstrap with Core providers
│   └── providers.php          # Additional providers
├── config/
│   └── core.php               # Core framework configuration
├── public/
│   └── index.php              # Web entry point
├── resources/
│   ├── css/app.css            # Tailwind entry point
│   ├── js/app.js              # JavaScript entry point
│   └── views/                 # Global Blade views
├── routes/
│   ├── web.php                # Fallback web routes
│   ├── api.php                # Fallback API routes
│   └── console.php            # Console command routes
└── tests/
    ├── Feature/               # HTTP/Livewire feature tests
    └── Unit/                  # Unit tests
```

## Bootstrap Process

The application bootstrap (`bootstrap/app.php`) registers Core PHP Framework providers:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Core\LifecycleEventProvider::class,  // Event system
        \Core\Website\Boot::class,            // Website components
        \Core\Front\Boot::class,              // Frontend (Livewire, Flux)
        \Core\Mod\Boot::class,                // Module discovery
    ])
    ->withRouting(...)
    ->withMiddleware(function (Middleware $middleware) {
        \Core\Front\Boot::middleware($middleware);
    })
    ->create();
```

### Provider Loading Order

1. **LifecycleEventProvider** - Sets up the event-driven architecture
2. **Website\Boot** - Registers website-level functionality
3. **Front\Boot** - Configures Livewire and frontend middleware
4. **Mod\Boot** - Discovers and loads modules from configured paths

## Module System

Modules are self-contained feature packages that register via lifecycle events. This is the core architectural pattern of the framework.

### Module Paths

Configured in `config/core.php`:

```php
'module_paths' => [
    app_path('Core'),     // Local framework overrides (EUPL-1.2)
    app_path('Mod'),      // Your application modules
    app_path('Website'),  // Website-specific code
],
```

### Module Structure

Each module lives in `app/Mod/{ModuleName}/` with a `Boot.php` entry point:

```
app/Mod/Blog/
├── Boot.php              # Event listeners (required)
├── Models/
│   └── Post.php          # Eloquent models
├── Routes/
│   ├── web.php           # Web routes
│   └── api.php           # API routes
├── Views/
│   └── posts/
│       └── index.blade.php
├── Livewire/
│   └── PostListPage.php  # Livewire components
├── Migrations/
│   └── 2025_01_01_create_posts_table.php
└── Tests/
    └── PostTest.php
```

### Boot.php Pattern

The `Boot.php` class declares which lifecycle events it responds to:

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
     * Event listeners - class is only instantiated when events fire
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
        // Register admin navigation
        $event->navigation('Blog', 'blog.admin.index', 'newspaper');

        // Register admin resources
        $event->resource('posts', PostResource::class);
    }

    public function onConsole(ConsoleBooting $event): void
    {
        // Register artisan commands
        $event->commands([
            ImportPostsCommand::class,
        ]);
    }
}
```

### Lifecycle Events

| Event | When Fired | Common Uses |
|-------|------------|-------------|
| `WebRoutesRegistering` | Web routes loading | Public routes, views |
| `ApiRoutesRegistering` | API routes loading | REST endpoints |
| `AdminPanelBooting` | Admin panel setup | Navigation, resources |
| `ClientRoutesRegistering` | Authenticated SaaS routes | Dashboard, settings |
| `ConsoleBooting` | Artisan bootstrapping | Commands, schedules |
| `McpToolsRegistering` | MCP server setup | AI agent tools |

### Lazy Loading

Modules are discovered at boot time, but their `Boot` classes are only instantiated when the events they listen to are fired. This means:

- Console commands don't load web routes
- API requests don't load admin panel code
- Unused modules have minimal overhead

## Dependency Packages

The template depends on four Core PHP Framework packages:

| Package | Namespace | Purpose |
|---------|-----------|---------|
| `host-uk/core` | `Core\` | Foundation: events, modules, lifecycle |
| `host-uk/core-admin` | `Core\Admin\` | Admin panel, Livewire modals, Flux UI |
| `host-uk/core-api` | `Core\Api\` | REST API, scopes, rate limiting, webhooks |
| `host-uk/core-mcp` | `Core\Mcp\` | Model Context Protocol for AI agents |

These are loaded as Composer dependencies and provide the framework infrastructure.

## Frontend Stack

### Livewire 3

Livewire components live within modules:

```php
// app/Mod/Blog/Livewire/PostListPage.php
<?php

declare(strict_types=1);

namespace App\Mod\Blog\Livewire;

use App\Mod\Blog\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PostListPage extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('blog::posts.index', [
            'posts' => Post::latest()->paginate(10),
        ]);
    }
}
```

### Flux UI

Flux Pro components are the standard UI library. Example usage:

```blade
<flux:modal name="edit-post">
    <flux:heading>Edit Post</flux:heading>
    <flux:input wire:model="title" label="Title" />
    <flux:textarea wire:model="content" label="Content" />
    <flux:button type="submit">Save</flux:button>
</flux:modal>
```

### Asset Pipeline

Vite handles asset compilation:

- `resources/css/app.css` - Tailwind CSS entry point
- `resources/js/app.js` - JavaScript entry point
- Module assets are not automatically included; import them in the main files or use `@vite` directive

## Configuration

### Core Framework (`config/core.php`)

```php
return [
    // Paths to scan for modules
    'module_paths' => [
        app_path('Core'),
        app_path('Mod'),
        app_path('Website'),
    ],

    // Service configuration
    'services' => [
        'cache_discovery' => env('CORE_CACHE_DISCOVERY', true),
    ],

    // CDN configuration
    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'driver' => env('CDN_DRIVER', 'bunny'),
    ],
];
```

### Environment Variables

Key Core-specific environment variables:

| Variable | Default | Description |
|----------|---------|-------------|
| `CORE_CACHE_DISCOVERY` | `true` | Cache module discovery for performance |
| `CDN_ENABLED` | `false` | Enable CDN for static assets |
| `CDN_DRIVER` | `bunny` | CDN provider (bunny, cloudflare, etc.) |
| `FLUX_LICENSE_KEY` | - | Flux Pro license key (optional) |

## Testing

Tests use Pest PHP and follow Laravel conventions:

```php
// tests/Feature/BlogTest.php
<?php

use App\Mod\Blog\Models\Post;

it('displays posts on the index page', function () {
    $posts = Post::factory()->count(3)->create();

    $this->get('/blog')
        ->assertOk()
        ->assertSee($posts->first()->title);
});

it('requires authentication to create posts', function () {
    $this->post('/blog', ['title' => 'Test'])
        ->assertRedirect('/login');
});
```

### Test Organisation

- **Feature tests** - HTTP requests, Livewire components, integration tests
- **Unit tests** - Services, utilities, isolated logic
- **Module tests** - Can live within the module directory (`app/Mod/Blog/Tests/`)

## Routing

### Route Registration

Routes are registered via module events, not the traditional `routes/` directory:

```php
// app/Mod/Blog/Routes/web.php
<?php

use App\Mod\Blog\Livewire\PostListPage;
use App\Mod\Blog\Livewire\PostShowPage;
use Illuminate\Support\Facades\Route;

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', PostListPage::class)->name('index');
    Route::get('/{post:slug}', PostShowPage::class)->name('show');
});
```

The `routes/web.php` and `routes/api.php` files are fallbacks for routes that don't belong to any module.

### View Namespacing

Module views are namespaced:

```blade
{{-- Accessing blog module views --}}
@include('blog::partials.header')

{{-- In a Livewire component --}}
return view('blog::posts.index', [...]);
```

## Namespace Conventions

| Path | Namespace | License |
|------|-----------|---------|
| `app/Core/` | `Core\` (local) | EUPL-1.2 |
| `app/Mod/` | `App\Mod\` | Your choice |
| `app/Website/` | `App\Website\` | Your choice |
| `vendor/host-uk/core/` | `Core\` | EUPL-1.2 |

The `app/Core/` directory is for local overrides of framework classes. Any class you place here will take precedence over the vendor package.
