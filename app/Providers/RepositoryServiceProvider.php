<?php

namespace App\Providers;

use App\Interface\Repositories\ProducRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Binding Product
        $this->app->bind(
            ProducRepositoryInterface::class,
            ProductRepository::class
        );

        // Binding
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
