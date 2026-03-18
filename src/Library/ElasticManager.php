<?php

namespace Devouted\ElasticIndexManager\Library;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElasticManager
{
    private array $connectionServices = [];
    private ?string $chosenService = null;
    private ?Client $client;

    public function __construct(
        private readonly ContainerInterface $container,
        ?Client                             $client = null
    )
    {
        $this->client = $client;
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
            $service = $this->container->get($filteredService);
            if ($service instanceof Client || $service instanceof ClientBuilder) {
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
        if (is_null($choice)) {
            $this->client = null;
            return;
        }
        $service = $this->container->get($choice);
        if ($service instanceof ClientBuilder) {
            $this->client = $service->build();
        } else {
            $this->client = $service;
        }
    }

    public function hasClient(): bool
    {
        return $this->client !== null;
    }

    public function getIndexList(): array
    {
        $indices = $this->client->cat()->indices([
            'index' => '*',
            'format' => 'json',
            'h' => [
                'health',
                'status',
                'index',
                'uuid',
                'pri',
                'rep',
                'docs.count',
                'docs.deleted',
                'store.size',
                'pri.store.size'
            ]
        ]);
        return $indices->asArray();
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
        return $this->client->indices()->getMapping(['index' => $name])->asArray();
    }

    public function getTemplateForIndexPattern($pattern): array
    {
        return $this->client->indices()->getIndexTemplate(['name' => $pattern])->asArray();
    }

    public function setTemplateForIndexPattern($pattern, $filename = 'index-template.json'): array
    {
        $template = file_get_contents($filename);
        $templateData = json_decode($template, true);

        $params = [
            'name' => $pattern,
            'body' => $templateData,
        ];
        return $this->client->indices()->putTemplate($params)->asArray();
    }

    public function getLastRecords(string $index, int $records = 10): array
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

        return $this->client->search($params)->asArray();
    }
}
