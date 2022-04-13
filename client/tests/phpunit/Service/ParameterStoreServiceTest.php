<?php

namespace App\Service;

use Aws\Ssm\SsmClient;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ParameterStoreServiceTest extends TestCase
{
    use ProphecyTrait;

    /** @test */
    public function getFeatureFlag()
    {
        $ssmClient = self::prophesize(SsmClient::class);
        $ssmClient
            ->getParameter(['Name' => '/flag-prefix/test-flag'])
            ->shouldBeCalled()
            ->willReturn(['Parameter' => ['Value' => 'result']]);

        $sut = new ParameterStoreService($ssmClient->reveal(), '/param-prefix/', '/flag-prefix/');

        self::assertEquals('result', $sut->getFeatureFlag('test-flag'));
    }

    /** @test */
    public function getParameter()
    {
        $ssmClient = self::prophesize(SsmClient::class);
        $ssmClient
            ->getParameter(['Name' => '/param-prefix/test-flag'])
            ->shouldBeCalled()
            ->willReturn(['Parameter' => ['Value' => 'result']]);

        $sut = new ParameterStoreService($ssmClient->reveal(), '/param-prefix/', '/flag-prefix/');

        self::assertEquals('result', $sut->getParameter('test-flag'));
    }

    /**
     * @dataProvider parameterDataProvider
     * @test
     */
    public function addParameter($parameterName, $parameterValue)
    {
        $ssmClient = self::prophesize(SsmClient::class);
        $parameterPrefix = '/param-prefix/';
        $ssmClient
            ->putParameter(Argument::exact(
                ['Name' => $parameterPrefix.$parameterName,
                    'Value' => $parameterValue, ]))
            ->shouldBeCalled()
            ->willReturn([
                'Tier' => 'Standard',
                'Version' => 10,
            ]);

        $sut = new ParameterStoreService($ssmClient->reveal(), $parameterPrefix, '/flag-prefix/');

        $sut->putParameter($parameterName, $parameterValue);
    }

    public function parameterDataProvider()
    {
        return [
            'document sync set to true' => ['document-sync', 1],
            'checklist sync set to false' => ['checklist-sync', 0],
        ];
    }
}
