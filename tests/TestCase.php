<?php

namespace Aslnbxrz\SimplePrefix\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Aslnbxrz\SimplePrefix\SimplePrefixServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SimplePrefixServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // SQLite in-memory
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('user_name')->nullable();
            $table->string('type')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });
    }
}