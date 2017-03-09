<?php
namespace Cyneek\LaravelMultipleStapler\Providers;


use Cyneek\LaravelMultipleStapler\Interfaces\LaravelStaplerInterface;
use Cyneek\LaravelMultipleStapler\Models\StaplerFiles;
use Illuminate\Support\ServiceProvider;


/**
 * Class LaravelMultipleStaplerProvider
 *
 * Provider that will load all the necessary data.
 *
 * @author Joseba Juániz <joseba.juaniz@gmail.com>
 */
class LaravelMultipleStaplerProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LaravelStaplerInterface::class, StaplerFiles::class);

        // Publish migrations
        $migrations = realpath(__DIR__.'/../Migrations');

        $this->publishes([
            $migrations => $this->app->databasePath().'/migrations',
        ], 'migrations');

    }
}