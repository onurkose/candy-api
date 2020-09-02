<?php

namespace GetCandy\Api\Core\Collections\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Collections\Models\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            'url' => 'required',
        ];
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

        // $urls = $this->getUniqueUrl($data['url']);

        // $collection->routes()->createMany($urls);
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
        if (! $result) {
            return $this->errorNotFound();
        }

        return new CollectionResource($result);
    }
}
