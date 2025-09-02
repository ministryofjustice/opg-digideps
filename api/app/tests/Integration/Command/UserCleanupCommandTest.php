<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UserCleanupCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('digideps:delete-zero-activity-users');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('delete_zero_activity_users - success - Deleted 0 lay user(s) that have never had any activity after 30 days of registration', $output);
    }
}
