<?php

namespace Devouted\ElasticIndexManager\Filter;

interface FilterInterface
{
    public function filter(array $indexList): array;
}