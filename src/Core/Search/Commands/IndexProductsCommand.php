<?php

namespace GetCandy\Api\Core\Search\Commands;

use Illuminate\Console\Command;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Jobs\ReindexSearchJob;
use GetCandy\Api\Core\Search\Actions\IndexProductsAction;

class IndexProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:products:index {batchsize=1000} {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindexes products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $batchsize = (int) $this->argument('batchsize');
        $total = Product::withoutGlobalScopes()->count();

        $this->output->text('Indexing ' . $total . ' products in ' . ceil($total / $batchsize) . ' batches');
        $this->output->progressStart(ceil($total / $batchsize));
        Product::withoutGlobalScopes()->chunk($batchsize, function ($products, $index) {
            IndexProductsAction::run([
                'products' => $products,
            ]);
            dd(1);
            tap($this->output)->progressAdvance();
        });
        // $this->output->text('Indexing products');
        //
        // $search = app(SearchContract::class);

        // foreach ($this->indexables as $indexable) {
        //     $this->info('Indexing '.$indexable);
        //     $model = new $indexable;
        //     if ($this->option('queue')) {
        //         ReindexSearchJob::dispatch($indexable);
        //     } else {
        //         $search->indexer()->reindex($model, config('getcandy.search.batch_size', 1000));
        //     }
        // }

        // $this->info('Done!');
    }
}
