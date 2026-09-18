<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException as NotifyAPIException;
use OPG\Digideps\Frontend\Service\Availability\NotifyAvailability;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class NotifyAvailabilityTest extends TestCase
{
    public function testAvailable(): void
    {
        $notifyClient = self::createMock(NotifyClient::class);
        $notifyClient->expects(self::once())
            ->method('listTemplates')
            ->willReturn('[{"some valid JSON": "true"}]');

        $sut = new NotifyAvailability($notifyClient);
        $sut->ping();

        self::assertTrue($sut->isHealthy());
        self::assertEquals(null, $sut->getErrors());
    }

    public function testUnavailable(): void
    {
        $notifyClient = self::createMock(NotifyClient::class);
        $notifyClient->method('listTemplates')
            ->willThrowException(new NotifyAPIException(
                'HTTP:502',
                '502',
                ['errors' => [0 => ['error' => '502', 'message' => 'Not available']]],
                new Response(502)
            ));

        $sut = new NotifyAvailability($notifyClient);
        $sut->ping();

        self::assertFalse($sut->isHealthy());
        self::assertEquals('Notify - 502: "Not available"', $sut->getErrors());
    }
}
