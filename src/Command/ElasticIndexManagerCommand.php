<?php

namespace Devouted\ElasticIndexManager\Command;

use Devouted\ElasticIndexManager\Filter\FilterInterface;
use Devouted\ElasticIndexManager\Filter\FilterNotEmptyIndexes;
use Devouted\ElasticIndexManager\TableRenderer\TableRenderer;

use Elasticsearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;


#[AsCommand(
    name: 'elasticsearch:index:manage',
    description: 'Manage elasticsearch indexes, using defined service connections.',
)]
class ElasticIndexManagerCommand extends Command
{
    private array $connectionServices = [];
    private array $indexesList = [];
    private ?Client $client = null;
    private readonly QuestionHelper $helper;
    private ?FilterInterface $filter = null;

    public function __construct(
        private readonly ContainerInterface $container,
    )
    {
        $this->setConnectionList();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->helper = $this->getHelper('question');
        $running = true;
        $foundConnections = array_keys($this->connectionServices);
        while ($running) {
            $output->write("\033[2J\033[H");
            if (!is_null($this->client)) {
                $this->getAllIndexes($input, $output);
                continue;
            }

            $mainOptions = array_merge([
                "Exit"
            ], $foundConnections);
            $question = new ChoiceQuestion(
                'Choose an witch connection to use:',
                $mainOptions,
                0
            );

            $question->setErrorMessage('Invalid choice: %s');
            $choice = $this->helper->ask($input, $output, $question);

            switch ($choice) {
                case 'Exit':
                    $output->writeln('<info>Exiting...</info>');
                    $running = false;
                    break;
                default:
                    if (in_array($choice, $foundConnections)) {
                        $output->writeln(PHP_EOL . '<info>Using connection ' . $choice . '</info>');
                        $this->client = $this->container->get($choice);
                    } else {
                        $output->writeln('<error>Invalid choice. Try again.</error>');
                    }
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
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

    private function getAllIndexes(InputInterface $input, OutputInterface $output)
    {
        $indexes = $this->client->cat()->indices();

        if ($this->filter !== null) {
            $indexes = $this->filter->filter($indexes);
        }

        foreach ($indexes as $key => $index) {
            $index["ID"] = $key;
            $indexes[$key] = $index;
        }

        $this->indexesList = array_column($indexes, 'index');

        if (!empty($indexes)) {
            $renderer = new TableRenderer(array_keys($indexes[0]));
            $renderer->render($indexes);
        }
        $mainOptions = [
            "Back to connection list",
            "Reset filter",
            "Filter for empty indexes (docs.count = 0)",
            "Delete an index",
            "Delete all indexes by filter"
        ];

        $question = new ChoiceQuestion(
            'What action would you prefer',
            array_values($mainOptions),
            0
        );

        $question->setErrorMessage('Invalid choice: %s');
        $choice = $this->helper->ask($input, $output, $question);
        switch ($choice) {
            case 'Back to connection list':
                $this->client = $this->filter = null;
                $this->indexesList = [];
                break;
            case 'Filter for empty indexes (docs.count = 0)':
                $this->filter = new FilterNotEmptyIndexes();
                break;
            case 'Delete an index':
                $name = trim($this->helper->ask($input, $output, new Question("Please provide name or ID of the index: ")));
                if (is_string($name) && $name !== "" &&
                    (
                        (is_numeric($name) && key_exists($name, $indexes)) ||
                        in_array($name, $this->indexesList)
                    )
                ) {
                    $name = is_numeric($name) ? $this->indexesList[$name] : $name;
                    $output->writeln('<warning>Please confirm if you would like to delete index: ' . $name . '!</warning>');
                    if ($this->confirmationQuestion($input, $output)) {
                        $output->writeln('<info>Deleting index: ' . $name . '!</info>');
                        $this->client->indices()->delete(['index'=>$name]);
                        $output->writeln('<info>Deleted index: ' . $name . '! Reloading...</info>');
                        sleep(4);
                    }
                } else {
                    $output->writeln('<error>You did not provide a name nor ID of the index. Reloading....</error>');
                    sleep(5);
                }
                break;
            case 'Reset filter':
                $this->filter = null;
                break;
            case 'Delete all indexes by filter':
                if(is_null($this->filter)) {
                    $output->writeln('<error>You did not provide any filter. Reloading</error>');
                    sleep(2);
                }
                else{
                    $output->writeln('<comment>Indexes from list will be deleted!</comment>');
                    print_r($this->indexesList);
                    if ($this->confirmationQuestion($input, $output)) {
                        foreach($this->indexesList as $name){
                            $output->writeln('<info>Deleting index: ' . $name . '!</info>');
                            $this->client->indices()->delete(['index'=>$name]);
                            $output->writeln('<info>Deleted index: ' . $name . '</info>');
                        }
                        $output->writeln('<info>Reloading...</info>');
                        sleep(5);
                    }
                }
                break;
            default:
                break;
        }
    }

    private function confirmationQuestion($input, $output): bool
    {
        $question = new ConfirmationQuestion('Do you want to continue? (y/n) ', false);
        return $this->helper->ask($input, $output, $question);
    }

}