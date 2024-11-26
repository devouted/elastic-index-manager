<?php

namespace Devouted\ElasticIndexManager\ElasticManager;

use Elasticsearch\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ElasticManager
{
    private array $connectionServices = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly OutputInterface    $output,
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
        $this->output->writeln('<info>Deleting index: ' . $name . '!</info>');
        $this->client->indices()->delete(['index' => $name]);
        $this->output->writeln('<info>Deleted index: ' . $name . '! Reloading...</info>');
    }
}