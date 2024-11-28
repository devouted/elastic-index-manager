<?php

namespace Devouted\ElasticIndexManager\Views;

use Devouted\ElasticIndexManager\Dictionary\IndexMappingTableActions;
use Devouted\ElasticIndexManager\Library\ElasticManager;
use Devouted\ElasticIndexManager\Library\Messages;
use Devouted\ElasticIndexManager\Library\TableRenderer;
use Devouted\ElasticIndexManager\Questions\SelectIndexActionQuestion;
use Devouted\ElasticIndexManager\Questions\SelectIndexQuestion;
use Devouted\ElasticIndexManager\Questions\SelectIndexTableActionQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexMappingTableView
{
    use ViewTrait;

    private bool $running = true;

    public function __construct(
        private readonly InputInterface  $input,
        private readonly OutputInterface $output,
        private readonly QuestionHelper  $helper,
        private readonly ElasticManager  $elasticManager,
        private readonly string          $indexName
    )
    {
    }

    public function render(): void
    {
        $this->clearScreen();
        $mapping = $this->elasticManager->getMappingForIndex($this->indexName);
        $data = $mapping[$this->indexName]['mappings']['properties'] ?? [];
        $columns = ['Field', 'Type', 'Additional info'];
        $this->renderSettingsTable();
        $fields = $this->formatDataIntoTableFields($data);
        $this->renderDataTable($columns, $fields, 'Index mapping is empty');

        while ($this->running) {
            $choice = $this->ask(new SelectIndexActionQuestion());
            $this->runAction($choice);
        }
    }

    private function renderSettingsTable(): void
    {
        $renderer = new TableRenderer(['Applied settings']);
        $data = [
            ['<options=bold>Client</>', $this->elasticManager->getClientName()],
            ['<options=bold>Chosen Index</>', $this->indexName]
        ];
        $renderer->render($data);
    }

    private function formatDataIntoTableFields(mixed $data): array
    {
        $fields = [];
        foreach ($data as $field => $fieldDescription) {
            $type = $fieldDescription['type'] ?? "";
            unset($fieldDescription['type']);
            $fields[] = [
                $field,
                $type,
                !empty($fieldDescription) ? json_encode($fieldDescription) : ""
            ];
        }
        return $fields;
    }

    private function runAction(string $choice): void
    {
        switch ($choice) {
            case IndexMappingTableActions::BACK_TO_INDEX_LIST_TABLE->value:
                $this->running = false;
                break;
            case IndexMappingTableActions::SHOW_SAMPLE_DATA->value:
                $indexData = $this->elasticManager->getLastRecords($this->indexName);
                dump($indexData);
                $this->confirmationQuestion();
                break;
            case IndexMappingTableActions::DELETE_AN_INDEX->value:
                $this->deleteSingleIndexAction($this->indexName);
                $this->running = false;
                break;
            default:
            case 'NoAction':
                Messages::getInstance()->info('no action selected');
                break;
        }
    }
}