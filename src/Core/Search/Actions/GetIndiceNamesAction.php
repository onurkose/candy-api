<?php

namespace GetCandy\Api\Core\Search\Actions;

use Elastica\Client;
use Lorisleiva\Actions\Action;

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
        return [
            'filter' => 'string'
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

        $indexes = $client->getStatus()->getIndexNames();

        if (!$this->filter) {
            return $indexes;
        }

        return collect($indexes)->filter(function ($name) {
            return strpos($name, $this->filter) !== false;
        });
    }
}
