<?php

namespace App\Tests\Integration\Entity\Command;

use App\Command\SatisfactionPerformanceStatsCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SatisfactionPerformanceStatsCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $command = $app->find(SatisfactionPerformanceStatsCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccessful()
    {
        $this->commandTester->execute([]);
        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('satisfaction_performance_stats - success - Successfully extracted the satisfaction scores for Digideps', $output);
    }
}
