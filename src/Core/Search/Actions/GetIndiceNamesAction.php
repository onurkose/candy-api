<?php

namespace GetCandy\Api\Core\Search\Actions;

use DateTime;
use Elastica\Client;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Users\Actions\FetchUserAction;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Countries\Actions\FetchCountryAction;

class GetIndiceNamesAction extends Action
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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Index
     */
    public function handle()
    {
        $client = new Client(config('getcandy.search.client_config.elastic', []));
        return $client->getStatus()->getIndexNames();
    }
}
