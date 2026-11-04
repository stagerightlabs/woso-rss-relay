<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class StaticRoutesTest extends TestCase
{
    public function test_static_routes_do_not_use_the_session(): void
    {
        $this->get('/about')->assertOk()->assertCookieMissing('laravel_session');
    }

    public function test_production_responses_are_publicly_cacheable_with_an_etag(): void
    {
        $this->app['env'] = 'production';

        $response = $this->get('/about');

        $response->assertOk();
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=14400', $response->headers->get('Cache-Control'));
        $this->assertNotNull($response->headers->get('ETag'));
    }

    public function test_matching_etag_returns_not_modified(): void
    {
        $this->app['env'] = 'production';

        $etag = $this->get('/about')->headers->get('ETag');

        $this->get('/about', ['If-None-Match' => $etag])->assertStatus(304);
    }

    public function test_production_responses_include_security_headers(): void
    {
        $this->app['env'] = 'production';

        $this->get('/about')
            ->assertHeader('Strict-Transport-Security')
            ->assertHeader('Content-Security-Policy')
            ->assertHeader('Referrer-Policy');
    }

    public function test_non_production_responses_are_not_publicly_cached(): void
    {
        $response = $this->get('/about');

        $this->assertStringNotContainsString('public', $response->headers->get('Cache-Control'));
    }

    public function test_unsuccessful_responses_are_not_cached(): void
    {
        $this->app['env'] = 'production';

        $response = $this->get('/feed/not-a-real-site');

        $this->assertStringNotContainsString('max-age=14400', (string) $response->headers->get('Cache-Control'));
    }
}
