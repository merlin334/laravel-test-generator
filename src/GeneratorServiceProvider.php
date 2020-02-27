<?php

namespace BoggyBot\LaravelTestGenerator;

use BoggyBot\LaravelTestGenerator\Commands\GeneratorCommand;
use Illuminate\Support\ServiceProvider;

class GeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__).'/stubs' => $this->app->resourcePath('stubs/laravel-test-generator'),
        ], 'laravel-test-generator-stubs');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('BoggyBot.laravel-test-generator.command', function($app) {
            return $app[GeneratorCommand::class];
        });

        $this->commands('BoggyBot.laravel-test-generator.command');
    }

}
