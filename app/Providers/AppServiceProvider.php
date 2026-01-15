<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Elasticsearch driver for Scout
        $this->app->extend('scout.engine', fn ($manager, $app) => $manager->extend('elasticsearch', fn ($app) => new \Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine(
            \Elastic\Elasticsearch\ClientBuilder::create()
                ->setHosts(config('scout.elasticsearch.hosts'))
                ->build()
        )) ?? $manager);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Route::middleware('api')
             ->prefix('api')
             ->group(base_path('routes/api.php'));
    }
}
