<?php

namespace Devouted\ElasticIndexManager\Filter;

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
}