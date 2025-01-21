<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Remove reliance on the vite manifest file in the test environment
        $this->withoutVite();
    }

    /**
     * Retrieve the contents of a stub file.
     */
    public function stub(string $name): string
    {
        return file_get_contents(base_path("tests/stubs/{$name}"));
    }
}
