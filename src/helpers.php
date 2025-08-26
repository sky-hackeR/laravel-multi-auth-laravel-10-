<?php

if (! function_exists('multi_auth_studly')) {
    function multi_auth_studly(string $name): string
    {
        return \Illuminate\Support\Str::studly($name);
    }
}

if (! function_exists('multi_auth_slug')) {
    function multi_auth_slug(string $name): string
    {
        return \Illuminate\Support\Str::slug($name, '-');
    }
}
