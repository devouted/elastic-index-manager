<?php

namespace Devouted\ElasticIndexManager\Filter;

use Devouted\ElasticIndexManager\Dictionary\IndexTableActions;

class FilterNotEmptyIndexes implements FilterInterface
{
    public function filter(array $indexList): array
    {
        foreach ($indexList as $key => $index) {
            if ($index['docs.count'] !== "0") {
                unset($indexList[$key]);
            }
        }
        return array_values($indexList);
    }

    public function getName(): string
    {
        return '<fg=green>' . IndexTableActions::FILTER_EMPTY_INDEXES->value . '</>';
    }
}