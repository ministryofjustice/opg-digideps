<?php

namespace App\Tests\Sync\Command;

use App\Service\ParameterStoreService;
use App\Sync\Command\DocumentSyncCommand;
use App\Sync\Service\DocumentSyncRunner;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DocumentSyncRunnerTest extends KernelTestCase
{
    protected static ContainerInterface $container;
    private ParameterStoreService $parameterStore;
    private DocumentSyncRunner $documentSyncRunner;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->parameterStore = self::createMock(ParameterStoreService::class);
        $this->documentSyncRunner = self::createMock(DocumentSyncRunner::class);

        $app->add(new DocumentSyncCommand($this->parameterStore, $this->documentSyncRunner));

        $command = $app->find(DocumentSyncCommand::getDefaultName());
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->parameterStore
            ->expects(self::once())
            ->method('getFeatureFlag')
            ->with(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->willReturn('1');

        $this->parameterStore
            ->expects(self::once())
            ->method('getParameter')
            ->with(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT)
            ->willReturn('100');

        $this->documentSyncRunner->expects(self::once())
            ->method('run');

        $this->commandTester->execute([]);
    }

    public function testSleepsWhenTurnedOff()
    {
        $this->parameterStore
            ->expects(self::once())
            ->method('getFeatureFlag')
            ->with(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->willReturn('0');

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Feature disabled, sleeping', $output);
    }
}
