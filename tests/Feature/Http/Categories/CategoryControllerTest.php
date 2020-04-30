<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Categories\Models\Category;

/**
 * @group feature
 */
class CategoryControllerTest extends FeatureCase
{
    public function test_can_list_all_categories()
    {
        Category::create([
            'attribute_data' => [
               'webstore' => [
                    'en' => 'Test category'
               ]
            ]
        ]);

        $user = $this->admin();
        $response = $this->actingAs($user)->json('GET', 'categories');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/categories');
    }
}
