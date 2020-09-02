<?php

namespace GetCandy\Api\Core\Collections\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\Collections\Models\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Collections\Resources\CollectionResource;

class FetchCollection extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * The fetched address model.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new Collection)->decodeId($this->encoded_id);
        }

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
            'id' => 'integer|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.Collection::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel|null
     */
    public function handle()
    {
        try {
            $query = Collection::with($this->resolveEagerRelations());
            if ($this->handle) {
                return $query->whereHandle($this->handle)->firstOrFail();
            }

            return $query->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if ($this->runningAs('controller')) {
                return null;
            }
            throw $e;
        }
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
