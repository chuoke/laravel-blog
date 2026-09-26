<?php

namespace Chuoke\Blog\Tests;

use Chuoke\Blog\Facades\Blog;
use Chuoke\Blog\Providers\BlogServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            BlogServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Blog' => Blog::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
