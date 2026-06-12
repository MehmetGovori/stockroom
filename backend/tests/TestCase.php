<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return array<string, string>
     */
    protected function apiKeyHeaders(): array
    {
        return ['X-API-Key' => (string) config('stockroom.api_key')];
    }
}
