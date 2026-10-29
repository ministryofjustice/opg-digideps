<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use Aws\Result;
use Aws\Ssm\SsmClient;
use OPG\Digideps\Frontend\Service\ParameterStoreService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParameterStoreServiceTest extends TestCase
{
    public function testGetFeatureFlag(): void
    {
        $ssmClient = $this->getMockBuilder(SsmClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ssmClient->expects(self::once())
            ->method('__call')
            ->with('getParameter', [['Name' => '/flag-prefix/test-flag']])
            ->willReturn(['Parameter' => ['Value' => 'result']]);

        $sut = new ParameterStoreService($ssmClient, '/param-prefix/', '/flag-prefix/');

        self::assertEquals('result', $sut->getFeatureFlag('test-flag'));
    }

    public function testGetParameter(): void
    {
        $ssmClient = $this->getMockBuilder(SsmClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ssmClient->expects(self::once())
            ->method('__call')
            ->with('getParameter', [['Name' => '/param-prefix/test-flag']])
            ->willReturn(new Result(['Parameter' => ['Value' => 'result']]));

        $sut = new ParameterStoreService($ssmClient, '/param-prefix/', '/flag-prefix/');

        self::assertEquals('result', $sut->getParameter('test-flag'));
    }

    #[DataProvider('parameterDataProvider')]
    public function testPutFeatureFlag(string $flagName, string $flagValue): void
    {
        $ssmClient = $this->getMockBuilder(SsmClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $flagPrefix = '/flag-prefix/';

        $ssmClient->expects(self::once())
            ->method('__call')
            ->with('putParameter', [[
                'Name' => $flagPrefix . $flagName,
                'Value' => $flagValue,
                'Overwrite' => true,
            ]])
            ->willReturn(new Result(['Tier' => 'Standard', 'Version' => 10,]));

        $sut = new ParameterStoreService($ssmClient, '/param-prefix/', $flagPrefix);

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
