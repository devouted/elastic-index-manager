<?php

namespace Devouted\ElasticIndexManager\Library;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class TableRenderer
{
    private Table $table;

    public function __construct(array $columns)
    {
        $output = new ConsoleOutput();
        $this->table = new Table($output);
        $this->table->setHeaders($columns);
    }

    public function render($data): void
    {
        $this->table
            ->setRows($data)
            ->render();
    }
}