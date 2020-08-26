<?php

namespace GetCandy\Api\Core\Search\Traits;

use Elastica\Client;
use GetCandy\Api\Core\Search\Actions\CreateIndexAction;
use GetCandy\Api\Core\Search\Actions\UpdateMappingAction;
use GetCandy\Api\Core\Languages\Actions\FetchLanguagesAction;

trait InteractsWithIndexTrait
{
    /**
     * Gets a timestamped index.
     *
     * @param  mixed  $type
     * @return string
     */
    protected function getNewIndexName($type = 'products')
    {
        return config('getcandy.search.index_prefix', 'candy').
            '_'.
            $type;
    }

    protected function getNewAliases($mapping, $type = 'products')
    {
        $languages = FetchLanguagesAction::run();
        $indexName = $this->getNewIndexName();
        $suffix = microtime(true);
        $aliases = [];

        foreach ($languages as $language) {
            $alias = $indexName.'_'.$language->lang;
            $newIndex = $alias."_{$suffix}";
            $index = CreateIndexAction::run([
                'name' => $newIndex
            ]);
            UpdateMappingAction::run([
                'index' => $index,
                'mapping' => $mapping->getMapping(),
            ]);
            $aliases[$alias] = $newIndex;
        }

        return $aliases;
    }
}
