<?php

namespace Tests\Feature\Actions\Baskets;

use GetCandy\Api\Core\Baskets\Actions\AddBasketDiscount;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Discounts\Models\Discount;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Core\Discounts\Models\DiscountReward;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class DeleteBasketDiscountTest extends FeatureCase
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
        $basket->discounts()->attach($discount->id, ['coupon' => $discountCriteriaItem->value]);

        $attributes = [
            'encoded_id' => $basket->encoded_id,
            'encoded_discount_id' => $discount->encoded_id,
        ];

        $response = $this->actingAs($user)->json('DELETE', "baskets/{$basket->encoded_id}/discounts", $attributes);
        $response->assertStatus(200);

        // todo: OpenAPI spec...
       // $this->assertResponseValid($response, '/baskets/{basketId}/discounts', 'delete');
    }
}
