<?php

namespace App\Tests\Command;

use App\Command\UserRetentionPolicyCommand;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserRetentionPolicyCommandTest extends KernelTestCase
{
    use ProphecyTrait;

//    /** @var MockObject */
//    private retentionCommand;

    private ?string $output = null;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->retentionCommand = $this->createMock(UserRetentionPolicyCommand::class);

        $app->add(new UserRetentionPolicyCommand());

        $command = $app->find(UserRetentionPolicyCommand::getDefaultName());
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function testOutput()
    {
        $this
            ->invokeTest()
            ->assertCommandOutputContains('Hello World');
    }

    private function invokeTest(): self
    {
        $this->commandTester->execute([]);
        $this->output = $this->commandTester->getDisplay();

        return $this;
    }

    private function assertCommandOutputContains(string $outputContent): self
    {
        self::assertStringContainsString($outputContent, $this->output);

        return $this;
    }
}
