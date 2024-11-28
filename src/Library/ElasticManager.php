<?php

namespace Devouted\ElasticIndexManager\Library;

use Elasticsearch\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElasticManager
{
    private array $connectionServices = [];
    private ?string $chosenService = null;

    public function __construct(
        private readonly ContainerInterface $container,
        private ?Client                     $client = null
    )
    {
        $this->setConnectionList();
    }

    private function setConnectionList(): void
    {
        $serviceIds = $this->container->getServiceIds();
        $filteredServices = array_filter($serviceIds, function ($id) {
            return stripos($id, 'elastic') !== false;
        });
        $i = 0;
        foreach ($filteredServices as $filteredService) {
            if ($this->container->get($filteredService) instanceof Client) {
                $this->connectionServices[$filteredService] = [$i, $filteredService];
                $i++;
            }
        }
    }

    public function getConnectionServices(): array
    {
        return $this->connectionServices;
    }

    public function setClient(?string $choice = null): void
    {
        $this->chosenService = $choice;
        $this->client = (is_null($choice)) ? null : $this->container->get($choice);
    }

    public function hasClient(): bool
    {
        return $this->client !== null;
    }

    public function getIndexList(): array
    {
        return $this->client->cat()->indices();
    }

    public function deleteIndexes(array $indexesList = []): void
    {
        foreach ($indexesList as $name) {
            $this->deleteIndex($name);
        }
    }

    public function deleteIndex($name): void
    {
        Messages::getInstance()->warning('Deleting index: ' . $name . '!');
        $this->client->indices()->delete(['index' => $name]);
        Messages::getInstance()->success('Deleted index: ' . $name . '!');
    }

    public function getClientName(): ?string
    {
        return $this->chosenService;
    }

    public function getMappingForIndex(string $name): array
    {
        return $this->client->indices()->getMapping(['index' => $name]);
    }

    public function getTemplateForIndexPattern($pattern): array
    {
        return $this->client->indices()->getIndexTemplate(['name' => $pattern]);
    }

    public function setTemplateForIndexPattern($pattern, $filename = 'index-template.json'): array
    {
        $template = file_get_contents($filename);
        $templateData = json_decode($template, true);

        $params = [
            'name' => $pattern,
            'body' => $templateData,
        ];
        return $this->client->indices()->putTemplate($params);
    }

    public function getLastRecords(string $index, int $records = 10) : array
    {
        $params = [
            'index' => $index,
            'size' => $records,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ]
        ];

        return $this->client->search($params);
    }
}