<?php

namespace GetCandy\Api\Core\Baskets\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class SavedBasketCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = SavedBasketResource::class;
}
