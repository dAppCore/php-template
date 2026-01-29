<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Health Endpoint Tests
|--------------------------------------------------------------------------
|
| These tests verify that the health check endpoint is accessible and
| returns the expected response. The /up endpoint is used by load
| balancers and monitoring services to check application health.
|
*/

it('returns ok status from health endpoint', function () {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
