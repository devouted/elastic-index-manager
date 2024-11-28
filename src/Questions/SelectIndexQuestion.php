<?php

namespace Devouted\ElasticIndexManager\Questions;

use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectIndexQuestion extends ChoiceQuestion
{
    const CHOICE_BACK_TO_LIST = "Back to list";
    const QUESTION = "Chose index: ";

    public function __construct(array $columns)
    {
        $columns = array_merge([
            self::CHOICE_BACK_TO_LIST,
        ], $columns);
        parent::__construct(self::QUESTION, $columns, 0);
    }
}