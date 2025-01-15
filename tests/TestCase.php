<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Retrieve the contents of a stub file.
     */
    public function stub(string $name): string
    {
        return file_get_contents(base_path("tests/stubs/{$name}"));
    }
}
