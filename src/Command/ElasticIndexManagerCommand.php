<?php

namespace Devouted\ElasticIndexManager\Command;

use Devouted\ElasticIndexManager\Library\ElasticManager;
use Devouted\ElasticIndexManager\Library\Messages;
use Devouted\ElasticIndexManager\Views\SelectConnectionView;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[AsCommand(
    name: 'elasticsearch:index:manage',
    description: 'Manage elasticsearch indexes, using defined service connections.',
)]
class ElasticIndexManagerCommand extends Command
{
    public function __construct(
        private readonly ContainerInterface $container,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Messages::getInstance($output);
        $elasticManager = new ElasticManager($this->container);
        $helper = $this->getHelper('question');
        $selectConnectionView = new SelectConnectionView($helper, $input, $output, $elasticManager);
        $selectConnectionView->render();

        return Command::SUCCESS;
    }
}