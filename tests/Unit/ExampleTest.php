<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Example Unit Tests
|--------------------------------------------------------------------------
|
| Unit tests verify isolated functionality without database or HTTP
| requests. This file demonstrates the Pest testing syntax used in
| Core PHP Framework applications.
|
| Delete this file and add your own unit tests as needed.
|
*/

it('demonstrates basic expectations', function () {
    expect(true)->toBeTrue();
    expect(1 + 1)->toBe(2);
});

it('demonstrates string expectations', function () {
    $greeting = 'Hello, World!';

    expect($greeting)
        ->toBeString()
        ->toContain('Hello')
        ->toStartWith('Hello')
        ->toEndWith('!');
});

it('demonstrates array expectations', function () {
    $items = ['apple', 'banana', 'cherry'];

    expect($items)
        ->toBeArray()
        ->toHaveCount(3)
        ->toContain('banana');
});
