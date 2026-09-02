<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->app->detectEnvironment(fn (): string => 'production');
});

it('renders the Inertia error page for browser errors', function (int $status) {
    Route::get("/_test/error/{$status}", fn () => abort($status));

    $this->get("/_test/error/{$status}")
        ->assertStatus($status)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ErrorPage')
            ->where('status', $status));
})->with([403, 404, 500, 503]);

it('keeps JSON error responses for API clients', function () {
    Route::get('/_test/error/json', fn () => abort(404));

    $this->getJson('/_test/error/json')
        ->assertNotFound()
        ->assertJsonPath('message', '');
});
