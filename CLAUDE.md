# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Architecture

Modular monolith using Core PHP Framework (Laravel 12). Modules live in `app/Mod/{Name}/Boot.php` and register via events.

**Bootstrap chain:** `bootstrap/app.php` loads four Core providers in order:
1. `Core\LifecycleEventProvider` — fires lifecycle events that modules listen to
2. `Core\Website\Boot` — website layer
3. `Core\Front\Boot` — frontend/middleware layer
4. `Core\Mod\Boot` — discovers and registers all modules from paths in `config/core.php`

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

**Module paths (three layers):**
- `app/Core/` — framework-level overrides (EUPL-1.2 copyleft)
- `app/Mod/` — feature modules (your licence)
- `app/Website/` — website-specific features (your licence)

**Routing:** The top-level `routes/web.php` and `routes/api.php` are boilerplate stubs. All real routes are registered by modules via `Boot.php` event listeners.

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
composer test                        # Run all tests (Pest)
vendor/bin/pest tests/Feature        # Run feature tests only
vendor/bin/pest --filter="test name" # Run single test by name
vendor/bin/pest path/to/TestFile.php # Run single test file

# Code quality
composer lint                        # Fix code style (Pint)
vendor/bin/pint --dirty              # Format changed files only
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
| `lthn/php` | `Core\` | Framework core, events, module discovery |
| `lthn/php-admin` | `Core\Admin\` | Admin panel, Livewire modals |
| `lthn/api` | `Core\Api\` | REST API, scopes, rate limiting, webhooks |
| `lthn/php-mcp` | `Core\Mcp\` | Model Context Protocol for AI agents |

## Testing

- Pest 3 (not PHPUnit syntax)
- `RefreshDatabase` is auto-applied to all Feature tests via `tests/Pest.php`
- Tests use in-memory SQLite (`phpunit.xml`)
- CI runs against PHP 8.2, 8.3, 8.4

## Conventions

**Language:** UK English (colour, organisation, centre, behaviour, licence/license)

**PHP:**
- `declare(strict_types=1);` in all files
- Full type hints on parameters and return types
- Final classes by default unless inheritance is intended
- PSR-12 formatting (Laravel Pint)
- Don't create controllers for Livewire pages

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

**Indentation** (`.editorconfig`): 4 spaces for PHP, 2 spaces for JS/TS/JSON/YAML.

## Known Limitations

- `tailwind.config.js` only scans `resources/` — module view paths (`app/Mod/*/Views/`) are not yet included

## Licence

- `Core\` namespace and vendor packages: EUPL-1.2 (copyleft)
- `app/Mod/*`, `app/Website/*`: Your choice (no copyleft)