<?php

namespace AppBundle\Service;

use Aws\Ssm\SsmClient;
use PHPUnit\Framework\TestCase;

class FeatureFlagServiceTest extends TestCase
{
    public function testFeatureFlagGetsValue()
    {
        $ssmClient = self::prophesize(SsmClient::class);
        $ssmClient
            ->getParameter(['Name' => '/flag-prefix/test-flag'])
            ->shouldBeCalled()
            ->willReturn(['Parameter' => ['Value' => 'result']]);

        $sut = new FeatureFlagService($ssmClient->reveal(), '/flag-prefix/');

        self::assertEquals('result', $sut->get('test-flag'));
    }
}
