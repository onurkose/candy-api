<?php

namespace GetCandy\Api\Core\Baskets\Actions;

use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Resources\BasketResource;
use GetCandy\Api\Core\GetCandy;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class CreateBasket extends AbstractAction
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
            'meta' => 'nullable|array',
            'currency' => 'nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Baskets\Models\Basket|null
     */
    public function handle()
    {
        $basket = new Basket();

        if ($this->meta) {
            $basket->meta = $this->meta;
        }

        $basket->currency = $this->currency
            ? $this->currency
            : GetCandy::currencies()->getDefaultRecord()->code;

        $basket->save();

        event(new BasketStoredEvent($basket));

        return $basket;
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
        return new BasketResource($result);
    }
}
