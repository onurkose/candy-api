<?php

namespace GetCandy\Api\Core\Collections\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Routes\Actions\FetchRoute;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Customers\Actions\MapCustomerGroups;
use GetCandy\Api\Core\Collections\Resources\CollectionResource;

class CreateCollection extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-collections');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|valid_structure:collections',
            'slug' => 'required|string',
            'path' => 'nullable|string',
            'customer_groups' => 'nullable|array'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $existingRoute = FetchRoute::run([
                'slug' => $this->slug,
                'path' => $this->path,
            ]);
            if ($existingRoute) {
                $validator->errors()->add('field', 'Slug and/or Path already exist');
            }
        });
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        $collection = new Collection;
        $collection->attribute_data = $this->validated();
        $collection->save();

        $collection->routes()->create([
            'slug' => $this->slug,
            'path' => $this->path,
        ]);

        $groups = $this->customer_groups ?? [];

        // Create default attribute groups
        MapCustomerGroups::run([
            'use_defaults' => !count($groups),
            'model' => $collection,
            'groups' => $groups,
        ]);

        return $collection;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Collections\Models\Collection  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Collections\Resources\CollectionResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return new CollectionResource($result);
    }
}
