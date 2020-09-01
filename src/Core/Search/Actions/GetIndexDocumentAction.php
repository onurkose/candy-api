<?php

namespace GetCandy\Api\Core\Search\Actions;

use DateTime;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Countries\Actions\FetchCountryAction;
use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Users\Actions\FetchUserAction;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;

class GetIndexDocumentAction extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if (app()->runningInConsole()) {
            return true;
        }
        return $this->user()->can('index-records');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'indexable' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        return $this->indexable->getDocument();
    }

    /**
     * Gets a collection of indexables, based on a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    protected function getIndexables()
    {
        $attributes = $this->attributeMapping();
        dd($attributes);
        $customerGroups = GetCandy::customerGroups()->all();

        $indexables = collect();

        foreach ($attributes as $attribute) {
            foreach ($attribute as $lang => $item) {
                // Base Stuff
                $indexable = $this->getIndexable($model);

                $indice = $this->getIndexName()."_{$lang}_{$this->suffix}";

                $indexable->setIndex($indice);

                $categories = $this->getCategories($model);

                $indexable->set('departments', $categories->toArray());
                $indexable->set('customer_groups', $this->getCustomerGroups($model));
                $indexable->set('channels', $this->getChannels($model));
                $indexable->set('breadcrumbs', $categories->implode('name', ' | '));

                $groupPricing = [];

                if (! empty($item['data'])) {
                    foreach ($item['data'] as $field => $value) {
                        $indexable->set($field, (count($value) > 1 ? $value : $value[0]));
                    }
                }

                if ($model->variants) {
                    $pricing = [];
                    foreach ($customerGroups as $customerGroup) {
                        $prices = [];
                        $i = 0;

                        foreach ($model->variants as $variant) {
                            $price = $variant->customerPricing->filter(function ($item) use ($customerGroup) {
                                return $customerGroup->id == $item->group->id;
                            })->first();

                            $prices[] = $price ? $price->price : $variant->price;
                            $i++;
                        }

                        if (! count($prices)) {
                            continue;
                        }

                        $pricing[] = [
                            'id' => $customerGroup->encodedId(),
                            'name' => $customerGroup->name,
                            'min' => min($prices),
                            'max' => max($prices),
                        ];
                    }

                    $indexable->set('pricing', $pricing);

                    $skus = [];
                    foreach ($model->variants as $variant) {
                        $skus[] = $variant->sku;
                        if (! $indexable->min_price || $indexable->min_price > $variant->price) {
                            $indexable->set('min_price', $variant->price);
                        }
                        if (! $indexable->max_price || $indexable->max_price < $variant->price) {
                            $indexable->set('max_price', $variant->price);
                        }
                    }
                    $indexable->set('sku', $skus);
                }

                $indexables->push($indexable);
            }
        }

        return $indexables;
    }


}
