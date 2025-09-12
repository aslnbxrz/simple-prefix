<?php

namespace Aslnbxrz\SimplePrefix;

use Illuminate\Support\ServiceProvider;

class SimplePrefixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/simple-prefix.php', 'simple-prefix');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/simple-prefix.php' => config_path('simple-prefix.php'),
        ], 'simple-prefix-config');
    }
}