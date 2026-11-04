<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_it_shows_the_release_version_when_configured(): void
    {
        config([
            'relay.release_date' => '2025-01-20T01:12:08+00:00',
            'relay.release_commit' => 'bcdeb34',
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('v20250120')
            ->assertSee('bcdeb34')
            ->assertSee('&bull;', false);
    }

    public function test_it_renders_without_release_information(): void
    {
        config([
            'relay.release_date' => null,
            'relay.release_commit' => null,
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertDontSee('&bull;', false);
    }
}
