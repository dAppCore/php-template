<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| Configure Pest testing framework for the Core PHP Framework template.
| This file binds test traits to test cases and provides helper functions.
|
*/

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure passed to the "uses()" method binds an abstract test case
| to all Feature and Unit tests. The TestCase class provides a bridge
| between Laravel's testing utilities and Pest's expressive syntax.
|
*/

uses(TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Database Refresh
|--------------------------------------------------------------------------
|
| Apply RefreshDatabase to Feature tests that need a clean database state.
| Unit tests typically don't require database access.
|
*/

uses(RefreshDatabase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| You may extend Pest's expectations with custom methods here. These
| custom expectations can be used in any test throughout your application.
|
| For example:
| expect()->extend('toBeUkEnglish', function () {
|     return $this->not->toContain('color')
|         ->not->toContain('organization');
| });
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| You may define custom helper functions for your tests here. These
| functions will be available in all your test files.
|
| For example:
| function createWorkspace(array $attributes = []): Workspace {
|     return Workspace::factory()->create($attributes);
| }
|
*/
