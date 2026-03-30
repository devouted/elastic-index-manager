<?php

use Devouted\ElasticIndexManager\Filter\FilterByNameOfIndexes;
use Devouted\ElasticIndexManager\Filter\FilterNotEmptyIndexes;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    private array $sampleIndexes;

    protected function setUp(): void
    {
        $this->sampleIndexes = [
            ['index' => 'logs-2024-01', 'docs.count' => '100', 'store.size' => '1mb'],
            ['index' => 'logs-2024-02', 'docs.count' => '0', 'store.size' => '0b'],
            ['index' => 'metrics-2024-01', 'docs.count' => '500', 'store.size' => '5mb'],
            ['index' => 'metrics-2024-02', 'docs.count' => '0', 'store.size' => '0b'],
        ];
    }

    public function testFilterByNameMatchesPattern(): void
    {
        $filter = new FilterByNameOfIndexes('logs');
        $result = $filter->filter($this->sampleIndexes);

        $this->assertCount(2, $result);
        $this->assertEquals('logs-2024-01', $result[0]['index']);
        $this->assertEquals('logs-2024-02', $result[1]['index']);
    }

    public function testFilterByNameNoMatch(): void
    {
        $filter = new FilterByNameOfIndexes('nonexistent');
        $result = $filter->filter($this->sampleIndexes);

        $this->assertEmpty($result);
    }

    public function testFilterByNameReturnsName(): void
    {
        $filter = new FilterByNameOfIndexes('logs');
        $this->assertStringContainsString('logs', $filter->getName());
    }

    public function testFilterNotEmptyKeepsOnlyEmptyIndexes(): void
    {
        $filter = new FilterNotEmptyIndexes();
        $result = $filter->filter($this->sampleIndexes);

        $this->assertCount(2, $result);
        $this->assertEquals('0', $result[0]['docs.count']);
        $this->assertEquals('0', $result[1]['docs.count']);
    }

    public function testFilterNotEmptyReindexesArray(): void
    {
        $filter = new FilterNotEmptyIndexes();
        $result = $filter->filter($this->sampleIndexes);

        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey(1, $result);
        $this->assertArrayNotHasKey(2, $result);
    }

    public function testFilterNotEmptyAllNonEmpty(): void
    {
        $indexes = [
            ['index' => 'a', 'docs.count' => '10', 'store.size' => '1mb'],
            ['index' => 'b', 'docs.count' => '20', 'store.size' => '2mb'],
        ];

        $filter = new FilterNotEmptyIndexes();
        $result = $filter->filter($indexes);

        $this->assertEmpty($result);
    }
}
