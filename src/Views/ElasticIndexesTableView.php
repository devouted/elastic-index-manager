<?php

namespace Devouted\ElasticIndexManager\Views;

use Devouted\ElasticIndexManager\Dictionary\IndexActions;
use Devouted\ElasticIndexManager\ElasticManager\ElasticManager;
use Devouted\ElasticIndexManager\Filter\FilterByNameOfIndexes;
use Devouted\ElasticIndexManager\Filter\FilterInterface;
use Devouted\ElasticIndexManager\Filter\FilterNotEmptyIndexes;
use Devouted\ElasticIndexManager\Questions\SelectTableActionQuestion;
use Devouted\ElasticIndexManager\TableRenderer\TableRenderer;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ElasticIndexesTableView
{
    use ViewTrait;

    const ID_COLUMN = "ID";
    private ?FilterInterface $filter = null;
    private string $sortOrder = 'asc';
    private string $sortColumn = 'docs.count';

    public function __construct(
        private readonly InputInterface  $input,
        private readonly OutputInterface $output,
        private readonly ElasticManager  $elasticManager,
        private readonly QuestionHelper  $helper
    )
    {
    }

    public function render(): void
    {
        $indexes = $this->elasticManager->getIndexList();

        $columns = $this->getDefinedColumns($indexes);

        $this->appendFilterOnIndexes($indexes);
        $this->sortIndexes($indexes);
        $this->appendIdColumnToIndexes($indexes);

        $indexesList = $this->getIndexesList($indexes);

        if (!empty($indexes)) {
            $renderer = new TableRenderer($columns);
            $renderer->render($indexes);
        }

        $question = new SelectTableActionQuestion();
        $choice = $this->helper->ask($this->input, $this->output, $question);
        $this->runAction($choice, $indexesList, $indexes, $columns);
        $this->clearScreen();
    }

    private function sortIndexes(array &$indexes): void
    {
        usort($indexes, function ($a, $b) {
            return $this->sortOrder === 'asc' ? $a[$this->sortColumn] <=> $b[$this->sortColumn] : $b[$this->sortColumn] <=> $a[$this->sortColumn];
        });
    }

    private function appendIdColumnToIndexes(array &$indexes): void
    {
        foreach ($indexes as $key => $index) {
            $indexes[$key] = array_merge([self::ID_COLUMN => $key], $index);
        }
    }

    private function confirmationQuestion(): bool
    {
        $question = new ConfirmationQuestion('Do you want to continue? (y/n) ', false);
        return $this->helper->ask($this->input, $this->output, $question);
    }

    private function appendFilterOnIndexes(array &$indexes): void
    {
        if ($this->filter !== null) {
            $indexes = $this->filter->filter($indexes);
        }
    }

    private function getIndexesList(array $indexes): array
    {
        return empty($indexes) ? [] : array_column($indexes, 'index');
    }

    private function runAction(string $choice, array $indexesList, array $indexes, array $columns): void
    {
        switch ($choice) {
            case IndexActions::CHANGE_ELASTIC_CLIENT->value:
                $this->elasticManager->setClient();
                break;
            case IndexActions::FILTER_EMPTY_INDEXES->value:
                $this->filter = new FilterNotEmptyIndexes();
                break;
            case IndexActions::FILTER_BY_INDEX_PATTERN->value:
                $search = trim($this->helper->ask($this->input, $this->output, new Question("Please provide a string that will try to mach: ")));
                $this->filter = new FilterByNameOfIndexes($search);
                break;
            case IndexActions::DELETE_AN_INDEX->value:
                $name = trim($this->helper->ask($this->input, $this->output, new Question("Please provide name or " . self::ID_COLUMN . " of the index: ")));
                if (is_string($name) && $name !== "" &&
                    (
                        (is_numeric($name) && key_exists($name, $indexes)) ||
                        in_array($name, $indexesList)
                    )
                ) {
                    $name = is_numeric($name) ? $indexesList[$name] : $name;
                    $this->output->writeln('<comment>Please confirm if you would like to delete index: ' . $name . '!</comment>');
                    if ($this->confirmationQuestion()) {
                        $this->elasticManager->deleteIndex($name);
                        sleep(4);
                    }
                } else {
                    $this->output->writeln('<error>You did not provide a name nor ID of the index. Reloading....</error>');
                    sleep(5);
                }
                break;
            case IndexActions::RESET_FILTER->value:
                $this->filter = null;
                break;
            case IndexActions::SORT->value:
                $question = new ChoiceQuestion(
                    'Chose column',
                    $columns,
                    0
                );
                $this->sortColumn = $this->helper->ask($this->input, $this->output, $question);
                $question = new ChoiceQuestion(
                    'Chose order',
                    ['asc', 'desc'],
                    0
                );
                $this->sortOrder = $this->helper->ask($this->input, $this->output, $question);
                break;
            case IndexActions::DELETE_ALL_INDEXES_BY_FILTER->value:
                if (is_null($this->filter)) {
                    $this->output->writeln('<error>You did not provide any filter. Reloading</error>');
                } else {
                    $this->output->writeln('<comment>Indexes from list will be deleted!</comment>');
                    print_r($indexesList);
                    if ($this->confirmationQuestion()) {
                        $this->elasticManager->deleteIndexes($indexesList);
                        $this->output->writeln('<info>resetting filter and reloading...</info>');
                        $this->filter = null;
                        sleep(5);
                    }
                }
                break;
            default:
                break;
        }
    }

    private function getDefinedColumns(array $indexes): array
    {
        $cols = array_keys($indexes[0]);
        array_unshift($cols, self::ID_COLUMN);
        return $cols;
    }

}