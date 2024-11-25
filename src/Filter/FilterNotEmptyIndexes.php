<?php

namespace Devouted\ElasticIndexManager\Filter;

class FilterNotEmptyIndexes implements FilterInterface
{
    public static function filter(array $indexList): array
    {
        foreach ($indexList as $key => $index) {
            if ($index['docs.count'] !== "0") {
                unset($indexList[$key]);
            }
        }
        return array_values($indexList);
    }
}