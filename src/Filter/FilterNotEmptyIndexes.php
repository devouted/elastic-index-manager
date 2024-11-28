<?php

namespace Devouted\ElasticIndexManager\Filter;

use Devouted\ElasticIndexManager\Dictionary\IndexActions;

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
        return '<fg=green>' . IndexActions::FILTER_EMPTY_INDEXES->value . '</>';
    }
}