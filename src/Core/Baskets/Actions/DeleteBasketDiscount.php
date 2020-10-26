<?php

namespace GetCandy\Api\Core\Baskets\Actions;

use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Resources\BasketResource;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeleteBasketDiscount extends AbstractAction
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
            'encoded_discount_id' => 'required|string|hashid_is_valid:'.Discount::class,
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @param BasketFactory $factory
     * @return \GetCandy\Api\Core\Baskets\Models\Basket|null
     */
    public function handle(BasketFactory $factory)
    {
        try {
            $basket = FetchBasket::run([
                'encoded_id' => $this->encoded_id,
            ]);

            $discount = Discount::find((new Discount)->decodeId($this->encoded_discount_id));
            $basket->discounts()->detach($discount);


            $basket->refresh();

            return $factory->init($basket)->get();
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
