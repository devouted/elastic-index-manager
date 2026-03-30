<?php

use Devouted\ElasticIndexManager\Library\ListSorter;
use PHPUnit\Framework\TestCase;

class ListSorterTest extends TestCase
{
    private ListSorter $sorter;

    protected function setUp(): void
    {
        $this->sorter = new ListSorter();
    }

    public function testConvertToBytesBasicUnits(): void
    {
        $this->assertEquals(0, ListSorter::convertToBytes('0b'));
        $this->assertEquals(1024, ListSorter::convertToBytes('1kb'));
        $this->assertEquals(1048576, ListSorter::convertToBytes('1mb'));
        $this->assertEquals(1073741824, ListSorter::convertToBytes('1gb'));
        $this->assertEquals(1099511627776, ListSorter::convertToBytes('1tb'));
    }

    public function testConvertToBytesShortUnits(): void
    {
        $this->assertEquals(1024, ListSorter::convertToBytes('1k'));
        $this->assertEquals(1048576, ListSorter::convertToBytes('1m'));
        $this->assertEquals(1073741824, ListSorter::convertToBytes('1g'));
    }

    public function testConvertToBytesDecimalValues(): void
    {
        $this->assertEquals(2621440, ListSorter::convertToBytes('2.5mb'));
        $this->assertEquals(512, ListSorter::convertToBytes('0.5kb'));
    }

    public function testConvertToBytesInvalidFormatThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ListSorter::convertToBytes('invalid');
    }

    public function testSortIndexesByDocsCountAsc(): void
    {
        $indexes = [
            ['index' => 'b', 'docs.count' => '200', 'store.size' => '1mb'],
            ['index' => 'a', 'docs.count' => '50', 'store.size' => '2mb'],
            ['index' => 'c', 'docs.count' => '100', 'store.size' => '500kb'],
        ];

        $this->sorter->setSorting('docs.count', 'asc');
        $this->sorter->sortIndexes($indexes);

        $this->assertEquals('50', $indexes[0]['docs.count']);
        $this->assertEquals('100', $indexes[1]['docs.count']);
        $this->assertEquals('200', $indexes[2]['docs.count']);
    }

    public function testSortIndexesByDocsCountDesc(): void
    {
        $indexes = [
            ['index' => 'a', 'docs.count' => '50', 'store.size' => '1mb'],
            ['index' => 'b', 'docs.count' => '200', 'store.size' => '2mb'],
        ];

        $this->sorter->setSorting('docs.count', 'desc');
        $this->sorter->sortIndexes($indexes);

        $this->assertEquals('200', $indexes[0]['docs.count']);
        $this->assertEquals('50', $indexes[1]['docs.count']);
    }

    public function testSortIndexesBySizeColumn(): void
    {
        $indexes = [
            ['index' => 'a', 'docs.count' => '10', 'store.size' => '2mb'],
            ['index' => 'b', 'docs.count' => '20', 'store.size' => '500kb'],
            ['index' => 'c', 'docs.count' => '30', 'store.size' => '1gb'],
        ];

        $this->sorter->setSorting('store.size', 'asc');
        $this->sorter->sortIndexes($indexes);

        $this->assertEquals('b', $indexes[0]['index']);
        $this->assertEquals('a', $indexes[1]['index']);
        $this->assertEquals('c', $indexes[2]['index']);
    }

    public function testDefaultSorting(): void
    {
        $this->assertEquals('docs.count', $this->sorter->sortColumn);
        $this->assertEquals('asc', $this->sorter->sortOrder);
    }
}
