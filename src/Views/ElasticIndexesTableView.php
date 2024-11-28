<?php

namespace Devouted\ElasticIndexManager\Views;

use Devouted\ElasticIndexManager\Dictionary\IndexActions;
use Devouted\ElasticIndexManager\Filter\FilterByNameOfIndexes;
use Devouted\ElasticIndexManager\Filter\FilterInterface;
use Devouted\ElasticIndexManager\Filter\FilterNotEmptyIndexes;
use Devouted\ElasticIndexManager\Library\ElasticManager;
use Devouted\ElasticIndexManager\Library\ListSorter;
use Devouted\ElasticIndexManager\Library\Messages;
use Devouted\ElasticIndexManager\Library\TableRenderer;
use Devouted\ElasticIndexManager\Questions\SelectColumnQuestion;
use Devouted\ElasticIndexManager\Questions\SelectIndexQuestion;
use Devouted\ElasticIndexManager\Questions\SelectOrderQuestion;
use Devouted\ElasticIndexManager\Questions\SelectTableActionQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticIndexesTableView
{
    use ViewTrait;

    const ID_COLUMN = "ID";
    private ?FilterInterface $filter = null;
    private ListSorter $listSorter;

    public function __construct(
        private readonly InputInterface  $input,
        private readonly OutputInterface $output,
        private readonly ElasticManager  $elasticManager,
        private readonly QuestionHelper  $helper,
    )
    {
        $this->listSorter = new ListSorter();
    }

    public function render(): void
    {
        $indexes = $this->elasticManager->getIndexList();

        $columns = $this->getDefinedColumns($indexes);

        $this->appendFilterOnIndexes($indexes);
        $this->listSorter->sortIndexes($indexes);
        $this->appendIdColumnToIndexes($indexes);

        $indexesList = $this->getIndexesList($indexes);

        $this->renderSettingsTable();
        $this->renderDataTable($columns, $indexes);

        $choice = $this->ask(new SelectTableActionQuestion());
        $this->runAction($choice, $indexesList, $indexes, $columns);
        $this->clearScreen();
    }

    private function appendIdColumnToIndexes(array &$indexes): void
    {
        foreach ($indexes as $key => $index) {
            $indexes[$key] = array_merge([self::ID_COLUMN => $key], $index);
        }
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
                $search = $this->ask("Please provide a string that will try to mach: ");
                $this->filter = new FilterByNameOfIndexes($search);
                break;
            case IndexActions::DELETE_AN_INDEX->value:
                $name = $this->ask(new SelectIndexQuestion($indexesList));
                if ($name === SelectIndexQuestion::CHOICE_BACK_TO_LIST) {
                    break;
                }
                Messages::getInstance()->comment('Please confirm if you would like to delete index: ' . $name . '!');
                if ($this->confirmationQuestion()) {
                    $this->elasticManager->deleteIndex($name);
                    sleep(4);
                }
                break;
            case IndexActions::RESET_FILTER->value:
                $this->filter = null;
                break;
            case IndexActions::SHOW_INDEX_MAPPING->value:
                $name = $this->ask(new SelectIndexQuestion($indexesList));
                if ($name === SelectIndexQuestion::CHOICE_BACK_TO_LIST) {
                    break;
                }
                $mapping = $this->elasticManager->getMappingForIndex($name);
//                dd($name, $mapping);
                break;
            case IndexActions::SORT->value:
                $this->listSorter->setSorting(
                    $this->ask(new SelectColumnQuestion($columns)),
                    $this->ask(new SelectOrderQuestion())
                );
                break;
            case IndexActions::DELETE_ALL_INDEXES_BY_FILTER->value:
                if (!is_null($this->filter)) {
                    Messages::getInstance()->comment('Indexes from list will be deleted!');
                    print_r($indexesList);
                    if ($this->confirmationQuestion()) {
                        $this->elasticManager->deleteIndexes($indexesList);
                        Messages::getInstance()->info('Resetting filter and reloading...');
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

    private function renderSettingsTable(): void
    {
        $renderer = new TableRenderer(['Applied settings']);
        $data = [
            ['<options=bold>Client</>', $this->elasticManager->getClientName()],
            ['<options=bold>Filter</>', $this->filter?->getName() ?? "Not set"],
            ['<options=bold>Sort</>', $this->listSorter->sortColumn . " " . $this->listSorter->sortOrder]
        ];
        $renderer->render($data);
    }

    private function renderDataTable(array $columns, array $indexes): void
    {
        if (!empty($indexes)) {
            $renderer = new TableRenderer($columns);
            $renderer->render($indexes);
        } else {
            Messages::getInstance()->comment('There are no indexes available');
        }
    }
}