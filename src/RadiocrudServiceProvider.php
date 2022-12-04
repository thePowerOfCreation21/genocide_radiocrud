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
            __DIR__.'/../database/migrations/2022_08_11_170703_create_key_value_configs_table.php' => database_path('migrations')
        ], 'radiocrud_migrations');
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