<?php

namespace Devouted\ElasticIndexManager\Views;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

trait ViewTrait
{
    private function clearScreen(): void
    {
        $this->output->write("\033[2J\033[H");
    }

    private function ask(mixed $question): string
    {
        if (is_string($question)) {
            $question = new Question($question);
        }
        return trim($this->helper->ask($this->input, $this->output, $question));
    }

    private function confirmationQuestion(): bool
    {
        return $this->ask(new ConfirmationQuestion('Do you want to continue? (y/n) ', false));
    }
}