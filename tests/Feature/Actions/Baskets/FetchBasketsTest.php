<?php

namespace Tests\Feature\Actions\Baskets;

use GetCandy\Api\Core\Baskets\Models\Basket;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class FetchBasketsTest extends FeatureCase
{
    public function test_can_run_action_as_controller()
    {
        $user = $this->admin();
        factory(Basket::class, 10)->create();

        $response = $this->actingAs($user)->json('GET', "baskets");

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/baskets', 'get');
    }

    public function test_action_is_denied_for_default_user()
    {
        $user = factory(User::class)->create();
        factory(Basket::class, 10)->create();

        $response = $this->actingAs($user)->json('GET', "baskets");
        $response->assertStatus(403);
        $this->assertResponseValid($response, '/baskets', 'get');
    }
}
