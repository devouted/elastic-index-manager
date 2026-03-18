<?php

namespace Devouted\ElasticIndexManager\Views;

use Devouted\ElasticIndexManager\Library\Messages;
use Devouted\ElasticIndexManager\Library\TableRenderer;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

trait ViewTrait
{
    private function clearScreen(): void
    {
        $this->output->write("\033[2J\033[H");
    }

    private function ask(mixed $question): mixed
    {
        if (is_string($question)) {
            $question = new Question($question);
        }
        $answer = $this->helper->ask($this->input, $this->output, $question);
        return is_string($answer) ? trim($answer) : $answer;
    }

    private function confirmationQuestion(): bool
    {
        return (bool) $this->ask(new ConfirmationQuestion('Do you want to continue? (y/n) ', false));
    }

    private function renderDataTable(array $columns, array $data, string $emptyMessage = 'Nothing to render'): void
    {
        if (!empty($data)) {
            $renderer = new TableRenderer($columns);
            $renderer->render($data);
        } else {
            Messages::getInstance()->comment($emptyMessage);
        }
    }

    private function deleteSingleIndexAction($name): void
    {
        Messages::getInstance()->warning('Please confirm if you would like to delete index: ' . $name . '!');
        if ($this->confirmationQuestion()) {
            $this->elasticManager->deleteIndex($name);
            sleep(4);
        }
    }
}
