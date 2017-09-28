<?php

namespace AppBundle\Command;

use AppBundle\Service\DocumentService;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class DocumentCleanupCommandTest extends KernelTestCase
{
    public function setUp()
    {
        $this->redisMock = m::mock('Predis\Client');
        $this->documentService = m::mock(DocumentService::class);

        $kernel = static::createKernel();
        $kernel->boot();
        $kernel->getContainer()->set('snc_redis.default', $this->redisMock);
        $kernel->getContainer()->set('document_service', $this->documentService);

        $application = new Application($kernel);

        $application->add(new DocumentCleanupCommand());

        $command = $application->find('digideps:documents-cleanup');

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteNoArgs()
    {
        $this->commandTester->execute([]);
        $this->assertContains('only be executed from admin', $this->commandTester->getDisplay());
    }

    public function testExecuteLockRelease()
    {
        $this->redisMock->shouldReceive('del')->once();
        $this->commandTester->execute(['--skip-admin-check'=>true, '--release-lock'=>true]);
        $this->assertContains('Lock released', $this->commandTester->getDisplay());
    }

    public function testExecuteLocked()
    {
        $this->redisMock->shouldReceive('setnx')->once()->andReturn(0);
        $this->redisMock->shouldReceive('ttl')->once();
        $this->redisMock->shouldReceive('expire');

        $this->documentService->shouldReceive('removeSoftDeleted')->never();
        $this->documentService->shouldReceive('removeOldReportSubmissions')->never();

        $this->commandTester->execute(['--skip-admin-check'=>true]);
    }

    public function testExecute()
    {
        $this->redisMock->shouldReceive('setnx')->once()->andReturn(1);
        $this->redisMock->shouldReceive('expire');

        $this->documentService->shouldReceive('removeSoftDeleted')->once();
        $this->documentService->shouldReceive('removeOldReportSubmissions')->once();

        $this->redisMock->shouldReceive('del');

        $this->commandTester->execute(['--skip-admin-check'=>true]);
    }

    public function tearDown()
    {
        m::close();
    }
}
