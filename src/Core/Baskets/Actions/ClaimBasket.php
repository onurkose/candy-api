<?php

namespace GetCandy\Api\Core\Baskets\Actions;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Resources\BasketResource;
use GetCandy\Api\Core\GetCandy;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClaimBasket extends AbstractAction
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
     * @return \GetCandy\Api\Core\Baskets\Models\Basket|null
     */
    public function handle()
    {
        try {
            $basket = FetchBasket::run([
                'encoded_id' => $this->encoded_id,
            ]);

            $basket->user()->associate($this->user());
            $basket->save();

            return $basket;
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
