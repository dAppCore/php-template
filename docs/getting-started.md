---
title: Getting Started
description: Quick start guide for creating a new Core PHP Framework application
updated: 2026-01-29
---

# Getting Started

This guide walks you through creating your first application with Core PHP Framework using the core-template.

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- SQLite (default) or MySQL/PostgreSQL

## Installation

### 1. Clone the Template

```bash
git clone https://github.com/host-uk/core-template.git my-project
cd my-project
```

Or use Composer create-project (once published):

```bash
composer create-project host-uk/core-template my-project
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### 3. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 4. Start Development Server

```bash
# In one terminal - PHP server
php artisan serve

# In another terminal - Vite dev server
npm run dev
```

Visit http://localhost:8000 to see your application.

## Creating Your First Module

The Core PHP Framework uses a modular architecture. Features are organised as self-contained modules.

### Using the Artisan Command

```bash
# Create a full-featured module
php artisan make:mod Blog --all

# Or select specific features
php artisan make:mod Blog --web --api
```

### Manual Creation

1. Create the module directory:

```bash
mkdir -p app/Mod/Blog/{Models,Routes,Views,Livewire,Migrations,Tests}
```

2. Create `app/Mod/Blog/Boot.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mod\Blog;

use Core\Events\WebRoutesRegistering;

class Boot
{
    public static array $listens = [
        WebRoutesRegistering::class => 'onWebRoutes',
    ];

    public function onWebRoutes(WebRoutesRegistering $event): void
    {
        $event->routes(fn() => require __DIR__.'/Routes/web.php');
        $event->views('blog', __DIR__.'/Views');
    }
}
```

3. Create `app/Mod/Blog/Routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/blog', function () {
    return view('blog::index');
})->name('blog.index');
```

4. Create `app/Mod/Blog/Views/index.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog</title>
</head>
<body>
    <h1>Welcome to the Blog</h1>
</body>
</html>
```

Visit http://localhost:8000/blog to see your module in action.

## Adding a Model

Create `app/Mod/Blog/Models/Post.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mod\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
```

Create a migration in `app/Mod/Blog/Migrations/`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

## Adding a Livewire Component

Create `app/Mod/Blog/Livewire/PostListPage.php`:

```php
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

Update `app/Mod/Blog/Routes/web.php`:

```php
<?php

use App\Mod\Blog\Livewire\PostListPage;
use Illuminate\Support\Facades\Route;

Route::get('/blog', PostListPage::class)->name('blog.index');
```

Create `app/Mod/Blog/Views/posts/index.blade.php`:

```blade
<div>
    <h1 class="text-2xl font-bold mb-4">Blog Posts</h1>

    @forelse ($posts as $post)
        <article class="mb-4 p-4 border rounded">
            <h2 class="text-xl font-semibold">{{ $post->title }}</h2>
            <p class="text-gray-600">{{ Str::limit($post->content, 200) }}</p>
        </article>
    @empty
        <p>No posts yet.</p>
    @endforelse

    {{ $posts->links() }}
</div>
```

## Writing Tests

Create `app/Mod/Blog/Tests/PostTest.php`:

```php
<?php

use App\Mod\Blog\Models\Post;

it('displays the blog index', function () {
    $this->get('/blog')
        ->assertOk()
        ->assertSee('Blog Posts');
});

it('shows posts on the index page', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'This is a test post.',
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('Test Post');
});
```

Run tests:

```bash
vendor/bin/pest
```

## Code Formatting

Before committing, run Laravel Pint:

```bash
# Format changed files only
vendor/bin/pint --dirty

# Format all files
vendor/bin/pint
```

## Next Steps

- Read the [Architecture documentation](architecture.md) to understand the module system
- Review [Security considerations](security.md) before deploying
- Explore the [Core PHP Framework documentation](https://github.com/host-uk/core-php)
- Add the Admin Panel with `host-uk/core-admin`
- Build an API with `host-uk/core-api`

## Common Commands

```bash
# Development
php artisan serve              # Start PHP server
npm run dev                    # Start Vite with HMR
npm run build                  # Build for production

# Modules
php artisan make:mod Name      # Create a new module
php artisan make:mod Name --all # With all features

# Database
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Reset and re-run migrations
php artisan db:seed            # Run seeders

# Testing
vendor/bin/pest                # Run all tests
vendor/bin/pest --filter=Name  # Run specific test

# Code Quality
vendor/bin/pint                # Format code
vendor/bin/pint --test         # Check formatting without changes
```
