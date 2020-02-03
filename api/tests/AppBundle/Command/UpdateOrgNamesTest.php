<?php declare(strict_types=1);

namespace Tests\AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateOrgNamesTest extends KernelTestCase
{
    /** @dataProvider fileNameProvider */
    public function testExecute($fileName, $expectedOutput)
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('orgs:update:names');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['CSVName' => $fileName]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString($expectedOutput, $output);
    }

    public function fileNameProvider()
    {
        return [
            'File name exists' => ['orgNameUpdate.csv', 'Successfully updated 0, failed to update 2'],
            'File name does not exist' => ['nonExistentFile.csv', 'Could not find file with name nonExistentFile.csv'],
        ];
    }
}
