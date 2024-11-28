<?php

namespace Devouted\ElasticIndexManager\Library;

use InvalidArgumentException;

class ListSorter
{

    public string $sortOrder = 'asc';
    public string $sortColumn = 'docs.count';

    public function sortIndexes(array &$indexes): void
    {
        usort($indexes, function ($a, $b) {

            $valA = str_contains($this->sortColumn, 'size') ? $this->convertToBytes($a[$this->sortColumn]) : $a[$this->sortColumn];
            $valB = str_contains($this->sortColumn, 'size') ? $this->convertToBytes($b[$this->sortColumn]) : $b[$this->sortColumn];

            return $this->sortOrder === 'asc' ? $valA <=> $valB : $valB <=> $valA;
        });
    }

    private function convertToBytes(string $size): int
    {
        if (!preg_match('/^\d+(\.\d+)?[kKmMgGtTpP]?[bB]?$/', $size)) {
            throw new InvalidArgumentException("Unknown Format: $size");
        }

        $size = strtolower(trim($size));
        $number = (float)preg_replace('/[^0-9.]/', '', $size);
        $unit = preg_replace('/[0-9.]/', '', $size);

        $units = [
            '' => 0,
            'b' => 0,
            'k' => 1,
            'kb' => 1,
            'm' => 2,
            'mb' => 2,
            'g' => 3,
            'gb' => 3,
            't' => 4,
            'tb' => 4,
            'p' => 5,
            'pb' => 5,
        ];

        $factor = $units[$unit] ?? null;
        if ($factor === null) {
            throw new InvalidArgumentException("Unknown unit: $unit");
        }

        return (int)($number * pow(1024, $factor));
    }

    public function setSorting(mixed $column, mixed $order): void
    {
        $this->sortColumn = $column;
        $this->sortOrder = $order;
    }
}