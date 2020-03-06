<?php
namespace App\Tests\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\DocumentSyncService;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DocumentSyncCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        /** @var DocumentSyncService|ObjectProphecy $documentSyncService */
        $documentSyncService = self::prophesize(DocumentSyncService::class);
        $documentSyncService
            ->syncReportDocument(Argument::type(Document::class))
            ->shouldBeCalled();

        $kernel = static::bootKernel();
        $kernel->getContainer()->set(DocumentSyncService::class, $documentSyncService->reveal());
        $application = new Application($kernel);

        $command = $application->find('digideps:document-sync');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('1 documents to upload', $output);
    }
}
