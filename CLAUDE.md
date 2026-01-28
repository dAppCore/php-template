# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture

Modular monolith using Core PHP Framework (Laravel 12). Modules live in `app/Mod/{Name}/Boot.php` and register via events.

**Event-driven registration:**
```php
class Boot
{
    public static array $listens = [
        WebRoutesRegistering::class => 'onWebRoutes',
        ApiRoutesRegistering::class => 'onApiRoutes',
        AdminPanelBooting::class => 'onAdminPanel',
    ];

    public function onWebRoutes(WebRoutesRegistering $event): void
    {
        $event->routes(fn() => require __DIR__.'/Routes/web.php');
        $event->views('blog', __DIR__.'/Views');
    }
}
```

**Module paths:** `app/Core/`, `app/Mod/`, `app/Website/` (configured in `config/core.php`)

## Commands

```bash
# Development
php artisan serve                    # Laravel dev server
npm run dev                          # Vite with HMR
composer dev:packages                # Use local packages (composer.local.json)

# Module creation
php artisan make:mod Blog --all      # Full module (web, api, admin)
php artisan make:mod Blog --web      # Web routes only
php artisan make:mod Blog --api      # API routes only

# Testing
vendor/bin/pest                      # Run all tests
vendor/bin/pest tests/Feature        # Run feature tests only
vendor/bin/pest --filter="test name" # Run single test by name
vendor/bin/pest path/to/TestFile.php # Run single test file

# Code quality
vendor/bin/pint --dirty              # Format changed files only
vendor/bin/pint                      # Format all files
```

## Module Structure

```
app/Mod/Blog/
├── Boot.php              # Event listeners (required)
├── Models/               # Eloquent models
├── Routes/
│   ├── web.php          # Web routes
│   └── api.php          # API routes
├── Views/               # Blade templates (namespaced as 'blog::')
├── Livewire/            # Livewire components
├── Migrations/          # Auto-discovered migrations
└── Tests/               # Module tests
```

## Packages

| Package | Namespace | Purpose |
|---------|-----------|---------|
| `host-uk/core` | `Core\` | Framework core, events, module discovery |
| `host-uk/core-admin` | `Core\Admin\` | Admin panel, Livewire modals |
| `host-uk/core-api` | `Core\Api\` | REST API, scopes, rate limiting, webhooks |
| `host-uk/core-mcp` | `Core\Mcp\` | Model Context Protocol for AI agents |

## Conventions

**Language:** UK English (colour, organisation, centre, behaviour, licence/license)

**PHP:**
- `declare(strict_types=1);` in all files
- Full type hints on parameters and return types
- PSR-12 formatting (Laravel Pint)
- Pest for testing (not PHPUnit syntax)

**Naming:**
- Models: Singular PascalCase (`Post`)
- Tables: Plural snake_case (`posts`)
- Livewire Pages: `{Feature}Page`
- Livewire Modals: `{Feature}Modal`

**UI Stack:**
- Livewire 3 for reactive components
- Flux Pro for UI components (not vanilla Alpine)
- Font Awesome Pro for icons (not Heroicons)
- Tailwind CSS for styling

## License

- `Core\` namespace and vendor packages: EUPL-1.2 (copyleft)
- `app/Mod/*`, `app/Website/*`: Your choice (no copyleft)