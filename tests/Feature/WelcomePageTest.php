<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Welcome Page Tests
|--------------------------------------------------------------------------
|
| These tests verify that the welcome page is accessible and renders
| correctly. This is an example feature test demonstrating the patterns
| used in Core PHP Framework applications.
|
*/

it('displays the welcome page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns the welcome view', function () {
    $response = $this->get('/');

    $response->assertViewIs('welcome');
});
