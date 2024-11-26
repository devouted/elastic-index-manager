<?php

namespace Devouted\ElasticIndexManager\Views;

use Devouted\ElasticIndexManager\ElasticManager\ElasticManager;
use Devouted\ElasticIndexManager\Questions\SelectConnectionQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelectConnectionView
{
    use ViewTrait;
    public function __construct(
        private readonly QuestionHelper  $helper,
        private readonly InputInterface  $input,
        private readonly OutputInterface $output,
        private readonly ElasticManager  $elasticManager,
    )
    {
    }

    public function render(): void
    {
        $foundConnections = array_keys($this->elasticManager->getConnectionServices());
        $running = true;

        $this->clearScreen();

        while ($running) {

            if ($this->elasticManager->hasClient()) {
                $elasticIndexesTableView = new ElasticIndexesTableView($this->input, $this->output, $this->elasticManager, $this->helper);
                $elasticIndexesTableView->render();
                continue;
            }

            $question = new SelectConnectionQuestion($foundConnections);

            $choice = $this->helper->ask($this->input, $this->output, $question);

            switch ($choice) {
                case 'Exit':
                    $this->output->writeln('<info>Exiting...</info>');
                    $running = false;
                    break;
                default:
                    if (in_array($choice, $foundConnections)) {
                        $this->output->writeln(PHP_EOL . '<info>Using connection ' . $choice . '</info>');
                        $this->elasticManager->setClient($choice);
                    } else {
                        $this->output->writeln('<error>Invalid choice. Try again.</error>');
                    }
            }
            $this->clearScreen();
        }
    }

}