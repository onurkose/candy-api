<?php

namespace GetCandy\Api\Core\Collections\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Collections\Resources\CollectionCollection;

class FetchCollections extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->paginate = $this->paginate === null ?: $this->paginate;

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
            'per_page' => 'numeric|max:200',
            'paginate' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $includes = $this->resolveEagerRelations();

        if (! $this->paginate) {
            return Collection::with($includes)->get();
        }

        return Collection::with($includes)->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Collections\Models\Collection|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Collections\Resources\Collections\CollectionCollection
     */
    public function response($result, $request): CollectionCollection
    {
        return new CollectionCollection($result);
    }
}
