<?php

namespace Devouted\ElasticIndexManager\Questions;

use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectConnectionQuestion extends ChoiceQuestion
{
    const QUESTION = "Choose an witch connection to use:";

    public function __construct(array $services)
    {
        $choices = array_merge([
            "Exit"
        ], $services);
        parent::__construct(self::QUESTION, $choices, 0);
    }
}