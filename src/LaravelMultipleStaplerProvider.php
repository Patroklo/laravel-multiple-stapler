<?php
namespace Cyneek\LaravelMultipleStapler;


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
    }
}