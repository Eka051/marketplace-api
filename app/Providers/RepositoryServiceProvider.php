<?php

namespace App\Providers;

use App\Interfaces\Repositories\BrandRepositoryInterface;
use App\Interfaces\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Interfaces\Repositories\ShopRepositoryInterface;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Eloquent\CategoryRepository;
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

        // Binding Brand
        $this->app->bind(
            BrandRepositoryInterface::class,
            BrandRepository::class
        );

        // Binding Category
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
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
