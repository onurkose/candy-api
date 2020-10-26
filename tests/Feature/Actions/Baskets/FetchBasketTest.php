<?php

namespace Tests\Feature\Actions\Baskets;

use GetCandy\Api\Core\Baskets\Models\Basket;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchBasketTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        $basket = factory(Basket::class)->create();

        $response = $this->actingAs($user)->json('GET', "baskets/{$basket->encoded_id}");

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/baskets/{basketId}', 'get');
    }

    public function test_can_handle_not_found()
    {
        $user = $this->admin();
        $basket = factory(Basket::class)->create();
        $basket->delete();

        $response = $this->actingAs($user)->json('GET', "baskets/{$basket->encoded_id}");
        $response->assertStatus(404);
        $this->assertResponseValid($response, '/baskets/{basketId}', 'get');
    }
}
