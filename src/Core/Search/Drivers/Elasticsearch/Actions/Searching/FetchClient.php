<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Elastica\Client;
use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Index;

class FetchClient extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'config' => 'nullable|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elasticsearch\Client
     */
    public function handle()
    {
        return new Client(config('getcandy.elastic', $this->config ?: []));
    }
}
