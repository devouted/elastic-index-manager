<?php

namespace Devouted\ElasticIndexManager\Views;

use Devouted\ElasticIndexManager\Library\ElasticManager;
use Devouted\ElasticIndexManager\Library\Messages;
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

        $elasticIndexesTableView = new ElasticIndexesTableView($this->input, $this->output, $this->elasticManager, $this->helper);

        while ($running) {

            if ($this->elasticManager->hasClient()) {
                $elasticIndexesTableView->render();
                continue;
            }

            $choice = $this->ask(new SelectConnectionQuestion($foundConnections));

            switch ($choice) {
                case SelectConnectionQuestion::CHOICE_EXIT:
                    Messages::getInstance()->info('Exiting...');
                    $running = false;
                    break;
                default:
                    if (in_array($choice, $foundConnections)) {
                        $this->elasticManager->setClient($choice);
                    } else {
                        Messages::getInstance()->error('Invalid choice. Try again.');
                    }
            }
            $this->clearScreen();
        }
    }

}