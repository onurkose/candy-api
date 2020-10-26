<?php

namespace GetCandy\Api\Core\Baskets\Actions;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Resources\BasketResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchBasket extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * The fetched basket model.
     *
     * @var \GetCandy\Api\Core\Baskets\Models\Basket
     */
    protected $basket;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new Basket)->decodeId($this->encoded_id);
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
            'id' => 'integer|required_without_all:encoded_id,handle',
            'encoded_id' => 'string|hashid_is_valid:'.Basket::class.'|required_without_all:id,handle',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Baskets\Models\Basket|null
     */
    public function handle()
    {
        try {
            return Basket::findOrFail($this->id);
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
     * @param   $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Baskets\Resources\BasketResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new BasketResource($result);
    }
}
