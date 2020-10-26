<?php

namespace GetCandy\Api\Core\Baskets\Actions;

use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Resources\BasketResource;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddBasketDiscount extends AbstractAction
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
            'encoded_id' => 'required|string|hashid_is_valid:'.Basket::class,
            'coupon' => 'required|string',
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

            $discount = DiscountCriteriaItem::where('value', '=', $this->coupon)->first();
            if ($discount && ! $basket->discount($this->coupon)) {
                $discount->set->discount->increment('uses');
                $basket->discounts()->attach($discount->set->discount->id, ['coupon' => $this->coupon]);
            }

            $basket->load('discounts');

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
