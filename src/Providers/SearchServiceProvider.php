<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Search\Commands\IndexProductsCommand;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        // $this->app->singleton(SearchContract::class, function ($app) {
        //     return $app->make(config('getcandy.search.client'));
        // });

        // $this->app->bind('getcandy.saved_search', function ($app) {
        //     return $app->make(SavedSearchService::class);
        // });

        if ($this->app->runningInConsole()) {
            $this->commands([
                IndexProductsCommand::class
            ]);
        }
    }
}
