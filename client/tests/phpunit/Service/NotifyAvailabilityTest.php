<?php

namespace App\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException as NotifyAPIException;
use App\Service\Availability\NotifyAvailability;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class NotifyAvailabilityTest extends TestCase
{
    /**
     * @test
     */
    public function available()
    {
        /** @var NotifyClient&ObjectProphecy $notifyClient */
        $notifyClient = self::prophesize(NotifyClient::class);
        $notifyClient->listTemplates()->shouldBeCalled()->willReturn('[{"some valid JSON": "true"}]');

        $sut = new NotifyAvailability($notifyClient->reveal());
        $sut->ping();

        self::assertEquals(true, $sut->isHealthy());
        self::assertEquals(null, $sut->getErrors());
    }

    /**
     * @test
     */
    public function unavailable()
    {
        /** @var NotifyClient&ObjectProphecy $notifyClient */
        $notifyClient = self::prophesize(NotifyClient::class);
        $notifyClient->listTemplates()->shouldBeCalled()->willThrow($this->generateNotfyAPIException());

        $sut = new NotifyAvailability($notifyClient->reveal());
        $sut->ping();

        self::assertEquals(false, $sut->isHealthy());
        self::assertEquals('Notify - 502: "Not available"', $sut->getErrors());
    }

    private function generateNotfyAPIException()
    {
        return new NotifyAPIException(
            'HTTP:502',
            '502',
            ['errors' => [0 => ['error' => '502', 'message' => 'Not available']]],
            new Response(502)
        );
    }
}
