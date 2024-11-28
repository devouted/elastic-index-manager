<?php

namespace Devouted\ElasticIndexManager\Library;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

class Messages
{

    private static ?Messages $instance = null;

    private function __construct(private OutputInterface $output)
    {
        $style = new OutputFormatterStyle('white', 'red', ['bold']);
        $this->output->getFormatter()->setStyle('custom-warning', $style);
    }

    public static function getInstance(?OutputInterface $output = null): Messages
    {
        if (is_null(self::$instance)) {
            self::$instance = new Messages($output);
        }
        return self::$instance;
    }

    public function comment(string $message): void
    {
        $this->writeToOutput($message);
    }

    public function info(string $message): void
    {
        $this->writeToOutput($message, 'info');
    }

    public function success(string $message): void
    {
        $this->writeToOutput($message);
    }

    public function warning(string $message): void
    {
        $this->writeToOutput($message);
    }

    public function error(string $message): void
    {
        $this->writeToOutput($message, 'error');
    }

    private function writeToOutput(string $message, string $type = "comment"): void
    {
        $this->output->writeln('<' . $type . '>' . $message . '!</' . $type . '>');
    }

}
