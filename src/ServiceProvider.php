<?php

namespace MWI\LaravelRefactor;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/database/' => database_path(),
        ], 'migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\Refactor::class
            ]);
        }
    }

    public function register()
    {
        //
    }
}
