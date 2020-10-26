<?php

namespace Tests\Feature\Actions\Baskets;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Core\Discounts\Models\DiscountReward;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class AddBasketDiscountTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $this->withExceptionHandling();
        $user = $this->admin();
        $basket = factory(Basket::class)->create();
        $discount = factory(Discount::class)->create();
        $discountCriteriaSet = factory(DiscountCriteriaSet::class)->create(['discount_id' => $discount->id]);
        $discountCriteriaItem = factory(DiscountCriteriaItem::class)->create([
            'discount_criteria_set_id' => $discountCriteriaSet->id
        ]);
        $discountReward = factory(DiscountReward::class)->create(['discount_id' => $discount->id]);

        /**
         *             'encoded_id' => 'required|string|hashid_is_valid:'.Basket::class,
        'coupon' => 'required|string',
         */

        $attributes = [
            'encoded_id' => $basket->encoded_id,
            'coupon' => $discountCriteriaItem->value,
        ];

        $response = $this->actingAs($user)->json('PUT', "baskets/{$basket->encoded_id}/discounts", $attributes);
dump($response);
        $response->assertStatus(200);
        //$this->assertResponseValid($response, '/baskets/{basketId}/meta', 'post');
    }

    public function test_can_handle_not_found()
    {
        $user = $this->admin();
        $basket = factory(Basket::class)->create();
        $basket->delete();

        $attributes = [
            'key' => 'requires_delivery',
            'value' => true,
        ];

        $response = $this->actingAs($user)->json('POST', "baskets/{$basket->encoded_id}/meta", $attributes);
        $response->assertStatus(404);
        $this->assertResponseValid($response, '/baskets/{basketId}/meta', 'post');
    }
}
