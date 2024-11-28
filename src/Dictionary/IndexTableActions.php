<?php

namespace Devouted\ElasticIndexManager\Dictionary;
enum IndexTableActions: string
{
    case CHANGE_ELASTIC_CLIENT = 'Change Elastic Client';
    case FILTER_EMPTY_INDEXES = 'Filter for empty indexes (docs.count = 0)';
    case FILTER_BY_INDEX_PATTERN = 'Filter by index pattern';
    case RESET_FILTER = 'Reset filter';
    case SORT = 'Sort';
    case SHOW_INDEX_MAPPING = 'Show Index Mapping';
    case DELETE_AN_INDEX = 'Delete an index';
    case DELETE_ALL_INDEXES_BY_FILTER = 'Delete all indexes by filter';
}
