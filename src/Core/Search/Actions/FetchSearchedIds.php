<?php

namespace GetCandy\Api\Core\Search\Actions;

use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Search\Contracts\SearchManagerContract;

class FetchSearchedIds extends Action
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
            'model' => 'required',
            'encoded_ids' => 'array|min:0',
            'includes' => 'array|min:0'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        $model = new $this->model;
        $parsedIds = $this->delegateTo(DecodeIds::class);
        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $model->with($this->includes ?? [])->whereIn("{$model->getTable()}.id", $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field({$model->getTable()}.id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }
}
