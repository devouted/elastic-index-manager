<?php

namespace Devouted\ElasticIndexManager\Filter;

interface FilterInterface
{
    public static function filter(array $indexList): array;
}