<?php

use Devouted\ElasticIndexManager\Command\ElasticIndexManagerCommand;
use Devouted\ElasticIndexManager\Library\ElasticManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @group integration
 */
class ElasticIndexManagerCommandTest extends KernelTestCase
{
    private function createTester(): ApplicationTester
    {
        $application = new Application(parent::bootKernel());
        $application->add(new ElasticIndexManagerCommand(parent::getContainer()));
        $application->setAutoExit(false);
        return new ApplicationTester($application);
    }

    public function testExecute(): void
    {
        $tester = $this->createTester();
        $tester->setInputs(['Exit']);

        $tester->run(['command' => 'elasticsearch:index:manage'], ['decorated' => false]);

        $output = $tester->getDisplay();

        $this->assertEquals(0, $tester->getStatusCode());
        $this->assertStringContainsString('Choose which connection to use:', $output);
        $this->assertStringContainsString('[0] Exit', $output);
        $this->assertStringContainsString('[1]', $output);
    }

    public function testCheckAllAvailableConnections(): void
    {
        $elasticManager = new ElasticManager(parent::getContainer());
        $services = array_keys($elasticManager->getConnectionServices());

        $this->assertIsArray($services);
        $this->assertNotEmpty($services);

        foreach ($services as $service) {
            $tester = $this->createTester();
            $tester->setInputs([$service, '0', '0']);
            $tester->run(['command' => 'elasticsearch:index:manage']);

            $output = $tester->getDisplay();
            $this->assertStringContainsString($service, $output);
            $this->assertStringContainsString('Sort', $output);
            $this->assertEquals(0, $tester->getStatusCode());
        }
    }
}