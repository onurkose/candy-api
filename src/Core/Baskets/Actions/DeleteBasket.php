<?php

namespace GetCandy\Api\Core\Baskets\Actions;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeleteBasket extends AbstractAction
{
    use ReturnsJsonResponses;

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
            'encoded_id' => 'required|string|hashid_is_valid:'.Basket::class,
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return bool
     */
    public function handle()
    {
        try {
            $basket = FetchBasket::run([
                'encoded_id' => $this->encoded_id,
            ]);

            if ($basket->order || $basket->user_id !== $this->user()->id) {
                return false;
            }

            $basket->lines()->delete();
            $basket->discounts()->delete();
            $basket->savedBasket()->delete();

            return $basket->delete();
        } catch (ModelNotFoundException $e) {
            if ($this->runningAs('controller')) {
                return false;
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
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->respondWithError();
        }

        return $this->respondWithNoContent();
    }
}
