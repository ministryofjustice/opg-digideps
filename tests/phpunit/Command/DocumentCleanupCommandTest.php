<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class DocumentCleanupCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);

        $application->add(new DocumentCleanupCommand());

        $command = $application->find('digideps:documents-cleanup');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $this->assertContains('only be executed from admin', $commandTester->getDisplay());
    }
}
