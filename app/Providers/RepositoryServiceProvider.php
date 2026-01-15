<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Interfaces\Repositories\ShopRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ShopRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Binding Product
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        // Binding Shop
        $this->app->bind(
            ShopRepositoryInterface::class,
            ShopRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
