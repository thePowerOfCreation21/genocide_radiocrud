<?php

namespace Genocide\Radiocrud;

use Illuminate\Support\ServiceProvider;

class RadiocrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations')
        ], 'radiocrud_migrations');

        $this->publishes([
            __DIR__.'/../Console/Commands' => app_path('Console/Commands')
        ], 'radiocrud_commands');

        $this->publishes([
            __DIR__.'/../stubs/Radiocrud' => base_path('stubs/Radiocrud')
        ], 'radiocrud_commands');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}