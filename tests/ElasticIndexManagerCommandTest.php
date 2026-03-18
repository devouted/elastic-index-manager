<?php

use Devouted\ElasticIndexManager\Command\ElasticIndexManagerCommand;
use Devouted\ElasticIndexManager\Library\ElasticManager;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

class ElasticIndexManagerCommandTest extends KernelTestCase
{

    private static ApplicationTester $tester;

    public static function setUpBeforeClass(): void
    {
        $application = new Application(parent::bootKernel());
        $application->add(new ElasticIndexManagerCommand(parent::getContainer()));
        $application->setAutoExit(false);
        $applicationTester = new ApplicationTester($application);

        self::$tester = $applicationTester;
    }

    public function testExecute(): void
    {
        $application = new Application(parent::bootKernel());
        $application->add(new ElasticIndexManagerCommand(parent::getContainer()));
        $application->setAutoExit(false);
        $applicationTester = new ApplicationTester($application);
        self::$tester = $applicationTester;
        self::$tester->setInputs(['Exit']);

        self::$tester->run(['command' => 'elasticsearch:index:manage'], ['decorated' => false]);

        $output = self::$tester->getDisplay();

        $this->assertEquals(0, self::$tester->getStatusCode());

        $this->assertStringContainsString('Choose an witch connection to use:', $output);
        $this->assertStringContainsString('[0] Exit', $output);
        $this->assertStringContainsString('[1]', $output);
    }

    public function testCheckAllAvailableconnections(): void
    {
        $application = new Application(parent::bootKernel());
        $application->add(new ElasticIndexManagerCommand(parent::getContainer()));
        $application->setAutoExit(false);
        $applicationTester = new ApplicationTester($application);
        $elasticManager = new ElasticManager(parent::getContainer());

        $services = array_keys($elasticManager->getConnectionServices());

        $this->assertIsArray($services);
        $this->assertNotEmpty($services);

        self::$tester = $applicationTester;

        foreach($services as $service) {
            $selections = [];
            $selections[] = $service;
            $selections[] = '0';
            $selections[] = '0';
            self::$tester->setInputs($selections);
            self::$tester->run(['command' => 'elasticsearch:index:manage']);
            $table = self::$tester->getDisplay();
//            var_dump($table);
//            $this->assertStringContainsString("Sort             | docs.count asc", $table);
            $this->assertStringContainsString($service, $table);
            $this->assertEquals(0, self::$tester->getStatusCode());
        }
    }
}