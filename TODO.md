# TODO - core-template

Project template for Core PHP Framework applications. This is the starter template developers clone to create new projects.

## P1 - Critical / Security

### Security Hardening

- [ ] **Add security headers middleware** - Configure `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`, and CSP headers. The template should ship with secure defaults that developers can customise.

- [ ] **Add CSRF protection documentation** - Document that Laravel's CSRF protection is enabled by default and how to handle AJAX requests with the X-CSRF-TOKEN header (already set up in `bootstrap.js` via axios).

- [ ] **Configure session security in .env.example** - Add `SESSION_SECURE_COOKIE=true` (commented for production) and document that `SESSION_ENCRYPT=true` should be enabled for sensitive applications.

- [ ] **Add rate limiting to default routes** - The welcome page has no rate limiting. Consider adding basic throttle middleware to prevent abuse during development/staging.

- [ ] **Document APP_KEY rotation** - Add a security note about key rotation and the implications for encrypted data (sessions, cookies).

### Environment Security

- [ ] **Add .env.production.example** - Provide a production-ready example with secure defaults (`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, etc.).

- [ ] **Add sensitive key validation** - Consider adding a boot-time check that warns if critical keys (APP_KEY, BCRYPT_ROUNDS) are using insecure defaults in production.

## P2 - High Priority

### Testing Infrastructure

- [x] **Add example tests** - Added example tests demonstrating Pest patterns. (Fixed: 2026-01-29)
  - `tests/Feature/WelcomePageTest.php` - Tests welcome page (GET / returns 200)
  - `tests/Feature/HealthEndpointTest.php` - Tests health endpoint (GET /up returns 200)
  - `tests/Unit/ExampleTest.php` - Demonstrates Pest expectations syntax

- [x] **Add Pest configuration file** - Created `tests/Pest.php` with TestCase binding, RefreshDatabase for Feature tests, and documentation for custom expectations/helpers. (Fixed: 2026-01-29)

- [ ] **Configure parallel testing** - Add `pest.xml` or configure phpunit.xml for parallel test execution.

- [ ] **Add database refresh trait documentation** - Document when to use `RefreshDatabase` vs `DatabaseMigrations` in tests.

### Developer Experience

- [x] **Add composer scripts** - Added common scripts to composer.json: `lint`, `test`, `test:coverage`. Also added `pestphp/pest-plugin-type-coverage` for coverage support. (Fixed: 2026-01-29)

- [ ] **Add make:mod command documentation** - The README mentions `php artisan make:mod` but doesn't document all available flags (--web, --api, --admin, --all).

- [ ] **Create example module** - Add a simple example module (e.g., `app/Mod/Example/`) that developers can reference or delete. This would demonstrate the module pattern better than documentation alone.

- [ ] **Add VS Code workspace settings** - Create `.vscode/settings.json` with recommended settings for PHP, Blade, and Tailwind.

- [ ] **Add EditorConfig** - Create `.editorconfig` for consistent formatting across different editors.

### Configuration

- [ ] **Document CDN configuration** - The `config/core.php` references CDN settings but there's no documentation on how to configure BunnyCDN or other CDN providers.

- [ ] **Add Flux Pro setup script** - Consider adding a composer script or artisan command to simplify Flux Pro installation for licensed users.

- [ ] **Add database configuration examples** - The .env.example shows SQLite as default with commented MySQL. Add PostgreSQL example too.

## P3 - Medium Priority

### Code Quality

- [ ] **Add strict_types to all PHP files** - The `AppServiceProvider.php`, `TestCase.php`, `DatabaseSeeder.php`, and route files are missing `declare(strict_types=1);`. This contradicts the coding standards documented in CLAUDE.md.

- [ ] **Add return type to artisan file** - The `artisan` file should have proper typing for consistency.

- [ ] **Standardise route file structure** - The `routes/api.php` and `routes/console.php` have comments but no actual routes. Consider adding example routes or removing the unused files entirely.

- [ ] **Add PHPStan/Larastan configuration** - Consider adding static analysis to catch type errors and potential bugs.

### Frontend

- [ ] **Add Livewire to Vite config** - The vite.config.js doesn't include Livewire-specific configuration for hot reloading.

- [ ] **Configure Tailwind for module paths** - The tailwind.config.js only scans `resources/` but modules in `app/Mod/*/Views/` won't be picked up. Add:
  ```js
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./app/Mod/**/Views/**/*.blade.php",
  ]
  ```

- [ ] **Add Flux UI styles import** - The `app.css` only imports Tailwind utilities. When using Flux, additional styles may be needed.

- [ ] **Remove welcome.blade.php inline styles** - The welcome page uses inline `<style>` tags instead of Tailwind classes. Consider converting to Tailwind or moving to a separate CSS file.

### CI/CD

- [ ] **Add missing cliff.toml** - The release.yml workflow references `cliff.toml` for changelog generation, but this file doesn't exist in the template.

- [ ] **Update actions/checkout version** - The CI workflows use `actions/checkout@v6` which may not exist yet. Verify and use the correct version (likely v4).

- [ ] **Add Codecov configuration** - Consider adding `codecov.yml` for customised coverage thresholds and ignore patterns.

- [ ] **Add branch protection documentation** - Document recommended GitHub branch protection rules for main branch.

### Documentation

- [ ] **Add CONTRIBUTING.md** - Guide for contributors including coding standards, PR process, and testing requirements.

- [ ] **Add CHANGELOG.md** - Start tracking changes in a changelog file.

- [ ] **Add LICENSE file** - The composer.json specifies EUPL-1.2 but there's no LICENSE file in the repository.

- [ ] **Improve README installation instructions** - Add troubleshooting section for common issues (permission errors, missing extensions).

## P4 - Low Priority

### Polish

- [ ] **Add favicon** - The public directory has no favicon. Add a default Core PHP Framework favicon.

- [ ] **Add meta tags to welcome.blade.php** - Missing description, Open Graph tags, and other SEO-relevant meta tags.

- [ ] **Configure error pages** - Add custom 404, 500, and 503 error pages that match the Core PHP Framework branding.

- [ ] **Add storage link documentation** - Document when and how to run `php artisan storage:link`.

- [ ] **Add Docker configuration** - Consider adding Dockerfile and docker-compose.yml for containerised development.

### Consistency

- [ ] **Unify AI instruction files** - There are three similar files: CLAUDE.md, GEMINI.md, and AGENTS.md. Consider consolidating into a single AI_INSTRUCTIONS.md or keeping them but ensuring they stay in sync.

- [ ] **Add .gitignore entries for common IDEs** - The template could benefit from more comprehensive IDE ignore patterns (JetBrains, VS Code, Sublime, etc.).

- [ ] **Remove unused .idea directory files** - These should be in .gitignore, not committed to the template.

## P5 - Nice to Have

### Features

- [ ] **Add health check route customisation** - The `/up` health endpoint is hardcoded. Consider making this configurable.

- [ ] **Add deployment documentation** - Include guides for deploying to common platforms (Forge, Vapor, DigitalOcean, etc.).

- [ ] **Add make:mod stub customisation** - Allow developers to customise the stubs used by `make:mod` command.

- [ ] **Add queue worker configuration** - Document queue setup and add example Supervisor configuration.

- [ ] **Add scheduled task documentation** - Document how to set up cron for Laravel's scheduler.

### Tooling

- [ ] **Add pre-commit hooks** - Configure Husky or similar to run Pint before commits.

- [ ] **Add GitHub issue templates** - Create templates for bug reports and feature requests.

- [ ] **Add GitHub PR template** - Create a pull request template with checklist.

- [ ] **Add Dependabot auto-merge** - Configure auto-merge for minor/patch dependency updates.

## P6 - Future / Backlog

### Long-term Improvements

- [ ] **Add multi-language support** - Consider adding lang directory structure and documentation for i18n.

- [ ] **Add API documentation generation** - Integrate OpenAPI/Swagger documentation generation.

- [ ] **Add performance monitoring integration** - Document integration with Laravel Telescope, Debugbar, or similar.

- [ ] **Add logging configuration examples** - Document centralised logging setup (Papertrail, Logstash, etc.).

- [ ] **Add backup configuration** - Document and provide examples for database backup strategies.

---

## Completed

*Move items here when done, preserving them for reference.*

- [x] **Add example tests (P2-049)** - Added `tests/Feature/WelcomePageTest.php`, `tests/Feature/HealthEndpointTest.php`, and `tests/Unit/ExampleTest.php` demonstrating Pest testing patterns. (2026-01-29)
- [x] **Add Pest configuration file (P2-050)** - Created `tests/Pest.php` with TestCase binding, RefreshDatabase for Feature tests, and placeholder documentation for custom expectations and helper functions. (2026-01-29)
- [x] **Add composer scripts (P2-051)** - Added `lint`, `test`, and `test:coverage` scripts to composer.json. Also added `pestphp/pest-plugin-type-coverage` dependency. (2026-01-29)
