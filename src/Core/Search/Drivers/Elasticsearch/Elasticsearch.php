<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch;

use Illuminate\Contracts\Events\Dispatcher;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Drivers\AbstractSearchDriver;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\SetIndexLive;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\IndexProducts;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Events\IndexingCompleteEvent;

class Elasticsearch extends AbstractSearchDriver
{
    public function __construct(Dispatcher $events)
    {
        $events->listen(IndexingCompleteEvent::class, function ($event) {
            SetIndexLive::run([
                'indexes' => $event->indexes,
                'type' => $event->type,
            ]);
        });
    }
    public function index($documents, $final = false)
    {
        $type = get_class($documents->first());

        // app()->make(Dispatcher::class)->
        // $events->listen(ModelsImported::class, function ($event) use ($searchable) {
        //     $this->resultMessage($event->models, $searchable);
        // });

        switch ($type) {
            case Product::class:
                IndexProducts::run([
                    'products' => $documents,
                    'uuid' => $this->reference,
                    'final' => $final,
                ]);
                break;
            default:
            break;
        }
    }

    public function config()
    {
        return [
            'features' => [
                'faceting',
                'aggregates',
            ]
        ];
    }
}