<?php

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Client\ClientInterface;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ClientInterface::class, function () {
            return new GuzzleAdapter();
        });

        $this->app->singleton(Client::class, function ($app) {
            return ClientBuilder::create()
                ->setHosts([config('scout.elasticsearch.hosts.0')])
                ->setHttpClient($app->make(ClientInterface::class))
                ->build();
        });
    }

    public function boot()
    {
        //
    }
}
