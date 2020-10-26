<?php

namespace Tests\Feature\Actions\Baskets;

use GetCandy\Api\Core\Baskets\Models\Basket;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class AddBasketMetaTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $basket = factory(Basket::class)->create();

        $attributes = [
            'key' => 'requires_delivery',
            'value' => true,
        ];

        $response = $this->actingAs($user)->json('POST', "baskets/{$basket->encoded_id}/meta", $attributes);

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/baskets/{basketId}/meta', 'post');
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
