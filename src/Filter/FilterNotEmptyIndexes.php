<?php

namespace Devouted\ElasticIndexManager\Filter;

use Devouted\ElasticIndexManager\Dictionary\IndexTableActions;

class FilterNotEmptyIndexes implements FilterInterface
{
    public function filter(array $indexList): array
    {
        return array_values(array_filter($indexList, fn($index) => $index['docs.count'] === "0"));
    }

    public function getName(): string
    {
        return '<fg=green>' . IndexTableActions::FILTER_EMPTY_INDEXES->value . '</>';
    }
}