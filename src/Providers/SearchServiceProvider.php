<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Search\SearchManager;
use GetCandy\Api\Core\Search\Commands\IndexProductsCommand;
use GetCandy\Api\Core\Search\Contracts\SearchManagerContract;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SearchManagerContract::class, function ($app) {
            return new SearchManager($app);
        });

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
