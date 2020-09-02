<?php

namespace GetCandy\Api\Core\Routes\Actions;


use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchRoute extends AbstractAction
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
    public function rules(): array
    {
        return [
            'slug' => 'string',
            'path' => 'nullable|string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        return Route::whereSlug($this->slug)->wherePath($this->path)->first();
    }
}
