<?php

namespace Devouted\ElasticIndexManager\Questions;

use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectColumnQuestion extends ChoiceQuestion
{
    const QUESTION = "Chose column: ";

    public function __construct(array $columns)
    {
        parent::__construct(self::QUESTION, $columns, 0);
    }
}