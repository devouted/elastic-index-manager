<?php

namespace Devouted\ElasticIndexManager\Questions;

use Devouted\ElasticIndexManager\Dictionary\IndexActions;
use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectTableActionQuestion extends ChoiceQuestion
{
    const QUESTION = "What action would you prefer:";

    public function __construct()
    {
        $choices = array_map(fn($choice) => $choice->value, IndexActions::cases());
        parent::__construct(self::QUESTION, $choices, 0);
    }
}