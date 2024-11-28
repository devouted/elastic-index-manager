<?php

namespace Devouted\ElasticIndexManager\Questions;

use Devouted\ElasticIndexManager\Dictionary\IndexTableActions;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectIndexTableActionQuestion extends ChoiceQuestion
{
    const QUESTION = "What action would you prefer:";

    public function __construct()
    {
        $choices = array_map(fn($choice) => $choice->value, IndexTableActions::cases());
        parent::__construct(self::QUESTION, $choices, 0);
    }
}