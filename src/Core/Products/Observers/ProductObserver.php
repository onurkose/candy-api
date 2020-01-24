<?php

namespace GetCandy\Api\Core\Products\Observers;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Assets\Services\AssetService;

class ProductObserver
{
    /**
     * The asset server
     *
     * @var AssetService
     */
    protected $assets;

    public function __construct(AssetService $assets)
    {
        $this->assets = $assets;
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function deleted(Product $product)
    {
        if ($product->isForceDeleting()) {
            \Log::debug('Hit!');
            $product->channels()->detach();
            $product->collections()->detach();

            foreach ($product->assets as $asset) {
                $this->assets->delete($asset->encoded_id);
            }

            $product->assets()->forceDelete();
            $product->variants()->forceDelete();
            $product->categories()->detach();
            $product->routes()->forceDelete();
            $product->recommendations()->forceDelete();
        }
    }
}