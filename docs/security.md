---
title: Security
description: Security considerations and audit notes for core-template
updated: 2026-01-29
---

# Security

This document covers security considerations for applications built with core-template. It includes both framework-provided protections and recommendations for hardening your application.

## Built-in Protections

### CSRF Protection

Laravel's CSRF protection is enabled by default for all web routes. The template includes axios configuration that automatically attaches the CSRF token to AJAX requests:

```javascript
// resources/js/bootstrap.js
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

For forms, use the `@csrf` Blade directive:

```blade
<form method="POST" action="/posts">
    @csrf
    <!-- form fields -->
</form>
```

### XSS Protection

Blade's `{{ }}` syntax automatically escapes output. Use `{!! !!}` only for trusted HTML content.

### SQL Injection

Eloquent ORM and Query Builder use parameterised queries by default. Avoid raw queries where possible:

```php
// Safe - parameterised
User::where('email', $email)->first();

// Dangerous - raw SQL
DB::select("SELECT * FROM users WHERE email = '$email'");  // Don't do this
```

### Mass Assignment

Models should define `$fillable` or `$guarded` properties to prevent mass assignment vulnerabilities:

```php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'slug'];
}
```

### Password Hashing

The template configures bcrypt with 12 rounds by default (`BCRYPT_ROUNDS=12` in `.env.example`). This is appropriate for production use.

## Recommendations

### Security Headers

Add security headers via middleware. Create `app/Http/Middleware/SecurityHeaders.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (adjust as needed)
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"
        );

        return $response;
    }
}
```

Register in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    \Core\Front\Boot::middleware($middleware);
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

### Session Security

For production environments, update these settings in `.env`:

```env
SESSION_SECURE_COOKIE=true      # Only send cookies over HTTPS
SESSION_ENCRYPT=true            # Encrypt session data
SESSION_HTTP_ONLY=true          # Prevent JavaScript access to session cookie
SESSION_SAME_SITE=strict        # Strict same-site policy
```

### HTTPS Enforcement

Force HTTPS in production by adding to `AppServiceProvider`:

```php
public function boot(): void
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### Rate Limiting

The default welcome route has no rate limiting. For production, add throttle middleware:

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });
});
```

Configure custom rate limiters in `AppServiceProvider`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });

    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });
}
```

### APP_KEY Management

The `APP_KEY` is critical for encryption. Never:

- Commit it to version control
- Share it between environments
- Use predictable values

Rotate the key only when necessary, understanding that:

- Existing encrypted data becomes unreadable
- Active sessions are invalidated
- Signed URLs become invalid

### Debug Mode

Ensure `APP_DEBUG=false` in production. Debug mode exposes:

- Stack traces with file paths
- Environment variables
- Database queries

### Database Credentials

Never commit database credentials. Use environment variables:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
```

## Audit Checklist

### Before Deployment

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` is set and unique
- [ ] Session cookies are secure (`SESSION_SECURE_COOKIE=true`)
- [ ] HTTPS is enforced
- [ ] Security headers are configured
- [ ] Rate limiting is in place for sensitive endpoints
- [ ] `.env` is not accessible via web (check with `curl https://yoursite.com/.env`)
- [ ] Storage directory is not web-accessible
- [ ] Error pages don't leak sensitive information
- [ ] Database credentials are environment-specific
- [ ] Third-party API keys are not exposed in client-side code

### Authentication (if using core-tenant)

- [ ] Password reset tokens expire appropriately
- [ ] Login attempts are rate limited
- [ ] Account lockout is configured after failed attempts
- [ ] Two-factor authentication is available for sensitive accounts
- [ ] Session regeneration on login
- [ ] Session invalidation on logout

### API Security (if using core-api)

- [ ] API keys are properly scoped
- [ ] Rate limiting per API key
- [ ] Webhook signatures are verified
- [ ] CORS is configured appropriately
- [ ] Sensitive endpoints require authentication

## Dependencies

Keep dependencies updated to receive security patches:

```bash
# Check for outdated packages
composer outdated

# Update dependencies
composer update

# Check for known vulnerabilities
composer audit
```

The template includes Dependabot configuration (`.github/dependabot.yml`) to automate security updates.

## Reporting Security Issues

If you discover a security vulnerability in the Core PHP Framework:

1. **Do not** create a public GitHub issue
2. Email security concerns to the maintainers directly
3. Include detailed steps to reproduce
4. Allow reasonable time for a fix before public disclosure

## Resources

- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Snyk Security Advisories](https://security.snyk.io/)
