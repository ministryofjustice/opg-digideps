<?php
namespace App\Tests\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DocumentSyncCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $doc = new Document();
        $doc->setId(6789);

        /** @var RestClient|ObjectProphecy $restClient */
        $restClient = self::prophesize(RestClient::class);
        $restClient
            ->apiCall('get', 'document/queued', [], 'Report\\Document[]', Argument::type('array'), false)
            ->shouldBeCalled()
            ->willReturn([ $doc ]);

        /** @var DocumentSyncService|ObjectProphecy $documentSyncService */
        $documentSyncService = self::prophesize(DocumentSyncService::class);
        $documentSyncService
            ->syncReportDocument($doc)
            ->shouldBeCalled();

        $kernel = static::bootKernel();
        $application = new Application($kernel);

        /** @var ContainerInterface */
        $container = $kernel->getContainer();
        $container->set(DocumentSyncService::class, $documentSyncService->reveal());
        $container->set(RestClient::class, $restClient->reveal());

        $command = $application->find('digideps:document-sync');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('1 documents to upload', $output);
    }
}
