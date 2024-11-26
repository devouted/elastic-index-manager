<?php

namespace Devouted\ElasticIndexManager\Views;

trait ViewTrait
{

    private function clearScreen(): void
    {
        $this->output->write("\033[2J\033[H");
    }
}