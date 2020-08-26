<?php

namespace GetCandy\Api\Core\Search\Actions;

use DateTime;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Search\Mapping\ProductMapping;
use GetCandy\Api\Core\Users\Actions\FetchUserAction;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Search\Actions\GetIndiceNamesAction;
use GetCandy\Api\Core\Countries\Actions\FetchCountryAction;
use GetCandy\Api\Core\Search\Traits\InteractsWithIndexTrait;
use GetCandy\Api\Core\Languages\Actions\FetchLanguagesAction;

class IndexProductsAction extends Action
{
    use InteractsWithIndexTrait;

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
        $aliases = $this->getNewAliases(new ProductMapping, 'products');

        $indiceNames = GetIndiceNamesAction::run();
        foreach ($this->products as $product) {
            $document = GetProductIndexDocumentAction::run([
                'product' => $product,
            ]);
            dd($document);
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
