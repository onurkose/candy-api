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

class CreateIndexAction extends Action
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
            'name' => 'string|required',
            'settings' => 'array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Index
     */
    public function handle()
    {
        $client = new Client(config('getcandy.search.client_config.elastic', []));
        $index = $client->getIndex($this->name);
        $index->create([
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'trigram' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['shingle'],
                        ],
                        'standard_lowercase' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase'],
                        ],
                        'candy' => [
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase', 'stop', 'porter_stem'],
                        ],
                    ],
                    'filter' => [
                        'shingle' => [
                            'type' => 'shingle',
                            'min_shingle_size' => 2,
                            'max_shingle_size' => 3,
                        ],
                    ],
                ],
            ],
        ]);
        return $index;
    }
}
