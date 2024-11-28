<?php

namespace Devouted\ElasticIndexManager\Filter;

use Devouted\ElasticIndexManager\Dictionary\IndexActions;

class FilterByNameOfIndexes implements FilterInterface
{
    public function __construct(private readonly string $search)
    {
    }

    public function filter(array $indexList): array
    {
        foreach ($indexList as $key => $index) {
            if (!str_contains($index['index'], $this->search)) {
                unset($indexList[$key]);
            }
        }
        return array_values($indexList);
    }

    public function getName(): string
    {
        return IndexActions::FILTER_BY_INDEX_PATTERN->value . ": <fg=green>" . $this->search . '</>';
    }

}