<?php

namespace GetCandy\Api\Core\Search\Actions;

use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Search\Indexables\ProductIndexable;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Search\Actions\GetIndiceNamesAction;

class IndexProductsAction extends AbstractIndexAction
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
        return $this->user()->can('index-products');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'products' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        $this->timestamp = microtime(true);

        $aliases = $this->getNewAliases(new ProductIndexable, 'products');

        $indiceNames = GetIndiceNamesAction::run([
            'filter' => $this->getNewIndexName()
        ]);

        foreach ($this->products as $product) {
            $documents = (new ProductIndexable($product))
                ->setIndexName($this->getNewIndexName())
                ->setSuffix($this->timestamp)
                ->getDocuments();
            dd($documents);
        }
        dd($this->products);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Addresses\Resources\AddressResource
     */
    public function response($result, $request)
    {
        return new AddressResource($result);
    }
}
