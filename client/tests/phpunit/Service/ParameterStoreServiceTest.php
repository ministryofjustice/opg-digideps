<?php

namespace AppBundle\Service;

use Aws\Ssm\SsmClient;
use PHPUnit\Framework\TestCase;

class ParameterStoreServiceTest extends TestCase
{
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
}
