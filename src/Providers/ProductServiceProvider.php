<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Products\Factories\ProductFactory;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;
use GetCandy\Api\Core\Products\Interfaces\ProductVariantInterface;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Products\Drafting\ProductDrafter;
use Drafting;

class ProductServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Drafting::extend('products', function ($app) {
            return $app->make(ProductDrafter::class);
        });
    }

    public function register()
    {
        $this->app->bind(ProductVariantInterface::class, function ($app) {
            return $app->make(ProductVariantFactory::class);
        });

        $this->app->bind(ProductInterface::class, function ($app) {
            return $app->make(ProductFactory::class);
        });
    }
}
