<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Elastica\Client;
use Elastica\Query\Term;
use Elastica\Query\DisMax;
use Elastica\Query\Wildcard;
use Elastica\Query\MultiMatch;
use Lorisleiva\Actions\Action;

class FetchTerm extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'term' => 'nullable|string',
            'type' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Query\Term
     */
    public function handle()
    {
        $ranking = config("getcandy.elastic.ranking.{$this->type}", []);

        $disMaxQuery = new DisMax;
        $disMaxQuery->setBoost(1.5);
        $disMaxQuery->setTieBreaker(1);

        if ($multiMatch = $ranking['multi_match'] ?? null) {

            $prev = null;

            foreach ($multiMatch['types'] ?? [] as $type => $fields) {
                if ($prev && is_string($fields)) {
                    $fields = $prev;
                }
                $multiMatchQuery = new MultiMatch;
                $multiMatchQuery->setType($type);
                $multiMatchQuery->setQuery($this->term);
                $multiMatchQuery->setOperator('and');
                $multiMatchQuery->setFields($fields);
                $disMaxQuery->addQuery($multiMatchQuery);
                if (is_array($fields)) {
                    $prev = $fields;
                }
            }
        }

        $skuTerm = strtolower($this->text);
        $wildcard = new Wildcard('sku.lowercase', "*{$skuTerm}*");
        $disMaxQuery->addQuery($wildcard);

        return $disMaxQuery;
    }
}