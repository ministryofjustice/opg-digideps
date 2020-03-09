<?php
namespace App\Tests\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\DocumentSyncService;
use Aws\Ssm\SsmClient;
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

        /** @var SsmClient|ObjectProphecy $ssmClient */
        $ssmClient = self::prophesize(SsmClient::class);
        $ssmClient
            ->getParameter([ 'Name' => '/default/flag/document-sync' ])
            ->shouldBeCalled()
            ->willReturn(['Parameter' => [
                'Name' => '/default/flag/document-sync',
                'Value' => '1'
            ]]);

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

        $kernel = static::bootKernel([ 'debug' => false ]);
        $application = new Application($kernel);

        /** @var ContainerInterface */
        $container = $kernel->getContainer();
        $container->set(DocumentSyncService::class, $documentSyncService->reveal());
        $container->set(RestClient::class, $restClient->reveal());
        $container->set(SsmClient::class, $ssmClient->reveal());

        $command = $application->find('digideps:document-sync');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('1 documents to upload', $output);
    }

    public function testSleepsWhenTurnedOff()
    {
        /** @var SsmClient|ObjectProphecy $ssmClient */
        $ssmClient = self::prophesize(SsmClient::class);
        $ssmClient
            ->getParameter([ 'Name' => '/default/flag/document-sync' ])
            ->shouldBeCalled()
            ->willReturn(['Parameter' => [
                'Name' => '/default/flag/document-sync',
                'Value' => '0'
            ]]);

        /** @var RestClient|ObjectProphecy $restClient */
        $restClient = self::prophesize(RestClient::class);
        $restClient
            ->apiCall(Argument::cetera())
            ->shouldNotBeCalled();

        /** @var DocumentSyncService|ObjectProphecy $documentSyncService */
        $documentSyncService = self::prophesize(DocumentSyncService::class);
        $documentSyncService
            ->syncReportDocument(Argument::cetera())
            ->shouldNotBeCalled();

        $kernel = static::bootKernel([ 'debug' => false ]);
        $application = new Application($kernel);

        /** @var ContainerInterface */
        $container = $kernel->getContainer();
        $container->set(DocumentSyncService::class, $documentSyncService->reveal());
        $container->set(RestClient::class, $restClient->reveal());
        $container->set(SsmClient::class, $ssmClient->reveal());

        $command = $application->find('digideps:document-sync');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Feature disabled, sleeping', $output);
    }
}
