<?php

use App\Support\Settings;

if (! function_exists('setting')) {
    /**
     * Read a site setting (with code default fallback).
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Settings::get($key, $default);
    }
}
