<?php

namespace Devouted\ElasticIndexManager\Questions;

use Symfony\Component\Console\Question\ChoiceQuestion;

class SelectOrderQuestion extends ChoiceQuestion
{
    const QUESTION = "Chose order: ";

    public function __construct(array $orders = ['asc', 'desc'])
    {
        parent::__construct(self::QUESTION, $orders, 0);
    }
}