<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use Aws\Result;
use Aws\Ssm\SsmClient;
use OPG\Digideps\Frontend\Service\ParameterStoreService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParameterStoreServiceTest extends TestCase
{
    private SsmClient&MockObject $ssmClient;

    public function setUp(): void
    {
        $this->ssmClient = $this->getMockBuilder(SsmClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['getParameter', 'putParameter'])
            ->getMock();
    }

    public function testGetFeatureFlag(): void
    {
        $this->ssmClient->expects(self::once())
            ->method('getParameter')
            ->with(['Name' => '/flag-prefix/test-flag'])
            ->willReturn(['Parameter' => ['Value' => 'result']]);

        $sut = new ParameterStoreService($this->ssmClient, '/param-prefix/', '/flag-prefix/');

        self::assertEquals('result', $sut->getFeatureFlag('test-flag'));
    }

    public function testGetParameter(): void
    {
        $this->ssmClient->expects(self::once())
            ->method('getParameter')
            ->with(['Name' => '/param-prefix/test-flag'])
            ->willReturn(new Result(['Parameter' => ['Value' => 'result']]));

        $sut = new ParameterStoreService($this->ssmClient, '/param-prefix/', '/flag-prefix/');

        self::assertEquals('result', $sut->getParameter('test-flag'));
    }

    /**
     * @dataProvider parameterDataProvider
     */
    public function testPutFeatureFlag(string $flagName, string $flagValue): void
    {
        $flagPrefix = '/flag-prefix/';

        $this->ssmClient->expects(self::once())
            ->method('putParameter')
            ->with([
                'Name' => $flagPrefix . $flagName,
                'Value' => $flagValue,
                'Overwrite' => true,
            ])
            ->willReturn(new Result(['Tier' => 'Standard', 'Version' => 10,]));

        $sut = new ParameterStoreService($this->ssmClient, '/param-prefix/', $flagPrefix);

        $sut->putFeatureFlag($flagName, $flagValue);
    }

    public static function parameterDataProvider(): array
    {
        return [
            'document sync set to true' => ['document-sync', '1'],
            'checklist sync set to false' => ['checklist-sync', '0'],
        ];
    }
}
