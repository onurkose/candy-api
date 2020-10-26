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
class CreateBasketTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();

        $attributes = [
            'currency' => 'GBP',
        ];

        $response = $this->actingAs($user)->json('POST', "baskets", $attributes);
        $response->assertStatus(201);

        // todo: OpenAPI spec...
        $this->assertResponseValid($response, '/baskets', 'post');
    }
}
