<?php

namespace Devouted\ElasticIndexManager\Questions;

use Devouted\ElasticIndexManager\Dictionary\IndexMappingTableActions;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectIndexActionQuestion extends ChoiceQuestion
{
    const QUESTION = "What action would you prefer for given index:";

    public function __construct()
    {
        $choices = array_map(fn($choice) => $choice->value, IndexMappingTableActions::cases());
        parent::__construct(self::QUESTION, $choices, 0);
    }
}