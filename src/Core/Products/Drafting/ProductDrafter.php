<?php

namespace GetCandy\Api\Core\Products\Drafting;

use DB;
use Storage;
use Versioning;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Illuminate\Database\Eloquent\Model;

class ProductDrafter implements DrafterInterface
{
    public function create(Model $product)
    {
        dd('Hello!');
    }

    public function publish(Model $product)
    {
        // Publish this product and remove the parent.
        $parent = $product->publishedParent;

        // Get any current versions and assign them to this new product.

        foreach ($parent->versions as $version) {
            $version->update([
                'versionable_id' => $product->id
            ]);
        }

        // Create a version of the parent before we publish these changes
        Versioning::with('products')->create($parent, null, $product->id);

        // Move the activities onto the draft
        $parent->activities->each(function ($a) use ($product) {
            $a->update(['subject_id' => $product->id]);
        });

        // Activate any product variants
        $variantIds = $product->variants->pluck('id')->toArray();

        DB::table('product_variants')
            ->whereIn('id', $variantIds)
            ->update([
                'drafted_at' => null
            ]);

        // Activate any routes
        $routeIds = $product->routes()->onlyDrafted()->get()->pluck('id')->toArray();

        DB::table('routes')
            ->whereIn('id', $routeIds)
            ->update([
                'drafted_at' => null
            ]);

        // Delete routes
        $parent->routes()->delete();

        $parent->forceDelete();

        $product->drafted_at = null;
        $product->save();

        return $product;
    }

        /**
     * Duplicate a product.
     *
     * @param Collection $data
     * @return Product
     */
    public function firstOrCreate(Model $product)
    {
        return $product->draft ?: DB::transaction(function () use ($product) {
            $product = $product->load([
                'variants',
                'categories',
                'routes',
                'channels',
                'customerGroups',
            ]);
            $newProduct = $product->replicate();
            $newProduct->drafted_at = now();
            $newProduct->draft_parent_id = $product->id;
            $newProduct->save();

            $product->variants->each(function ($v) use ($newProduct) {
                $new = $v->replicate();
                $new->product_id = $newProduct->id;
                $new->drafted_at = now();
                $new->draft_parent_id = $v->id;
                $new->save();
            });

            $product->routes->each(function ($r) use ($newProduct) {
                $new = $r->replicate();
                $new->element_id = $newProduct->id;
                $new->element_type = get_class($newProduct);
                $new->drafted_at = now();
                $new->draft_parent_id = $r->id;
                $new->save();
            });

            $product->attributes->each(function ($model) use ($newProduct) {
                $newProduct->attributes()->attach($model);
            });

            // Copy any activity log bits


            $newProduct->refresh();

            $this->processAssets($product, $newProduct);
            $this->processCategories($product, $newProduct);
            $this->processChannels($product, $newProduct);
            $this->processCustomerGroups($product, $newProduct);
            $newProduct->refresh();

            return $newProduct->load([
                'variants',
                'channels',
                'routes',
                'customerGroups',
            ]);
        });
    }

    /**
     * Process the assets for a duplicated product.
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processAssets($oldProduct, &$newProduct)
    {
        $currentAssets = $oldProduct->assets;
        $assets = collect();

        $currentAssets->each(function ($a) use ($assets, $newProduct) {
            $newAsset = $a->replicate();

            // Move the file to it's new location
            $newAsset->assetable_id = $newProduct->id;

            $newFilename = uniqid().'.'.$newAsset->extension;

            try {
                Storage::disk($newAsset->source->disk)->copy(
                    "{$newAsset->location}/{$newAsset->filename}",
                    "{$newAsset->location}/{$newFilename}"
                );
                $newAsset->filename = $newFilename;
            } catch (FileNotFoundException $e) {
                $newAsset->save();

                return;
            }

            $newAsset->save();

            foreach ($a->transforms as $transform) {
                $newTransform = $transform->replicate();
                $newTransform->asset_id = $newAsset->id;
                $newFilename = uniqid().'_'.$newTransform->filename;

                try {
                    Storage::disk($newAsset->source->disk)->copy(
                        "{$newTransform->location}/{$newTransform->filename}",
                        "{$newTransform->location}/{$newFilename}"
                    );
                } catch (FileNotFoundException $e) {
                    continue;
                }

                $newTransform->filename = $newFilename;
                $newTransform->save();
            }
        });
    }

    /**
     * Process the duplicated product categories.
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processCategories($oldProduct, &$newProduct)
    {
        $currentCategories = $oldProduct->categories;
        foreach ($currentCategories as $category) {
            $newProduct->categories()->attach($category);
        }
    }

    /**
     * Process the customer groups for the duplicated product.
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processCustomerGroups($oldProduct, &$newProduct)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $groups = $oldProduct->customerGroups;

        $newGroups = collect();

        foreach ($groups as $group) {
            // \DB::table()
            $newGroups->put($group->id, [
                'visible' => $group->pivot->visible,
                'purchasable' => $group->pivot->purchasable,
            ]);
        }

        $newProduct->customerGroups()->sync($newGroups->toArray());
    }

    /**
     * Process channels for a duplicated product.
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processChannels($oldProduct, &$newProduct)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $channels = $oldProduct->channels;

        $newChannels = collect();

        foreach ($channels as $channel) {
            $newChannels->put($channel->id, [
                'published_at' => now(),
            ]);
        }

        $newProduct->channels()->sync($newChannels->toArray());
    }
}