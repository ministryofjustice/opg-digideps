<?php

namespace AppBundle\Controller;

use AppBundle\Service\Availability\NotifyAvailability;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\WkHtmlToPdfGenerator;
use GuzzleHttp\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Mockery as m;

class ManageControllerTest extends AbstractControllerTestCase
{
    public static function availabilityProvider()
    {
        return [
            [true,  true, true,  true,  true, 200, 200, ['OK']], //all good
            [false, true, true,  true,  true, 200, 500, ['redis-error']],
            [true, false, true,  true,  true, 200, 500, ['api_errors']],
            [true,  true, false, true,  true, 200, 200, ['sirius_error']],
            [true,  true, true, false,  true, 200, 500, ['invalid key']],
            [true,  true, true,  true, false, 200, 500, ['wkhtmltopdf.isAlive']],
            [true,  true, true,  true, false, 500, 500, ['returned HTTP']],
        ];
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailability(
        $redisHealthy, $apiHealthy, $siriusHealthy, $notifyHealthy, $wkhtmltopdfError, $clamReturnCode,
        $statusCode, array $mustContain)
    {
        $container = $this->client->getContainer();

        //redis mock
        $redisMock = m::mock('Predis\Client');
        if ($redisHealthy) {
            $redisMock->shouldReceive('set')->with('RedisAvailabilityTestKey', 'valueSaved');
            $redisMock->shouldReceive('get')->with('RedisAvailabilityTestKey')->andReturn('valueSaved');
        } else {
            $redisMock->shouldReceive('set')->andThrow(new \RuntimeException('redis-error'));
        }
        $container->set('snc_redis.default', $redisMock);

        // api mock
        $this->restClient->get('manage/availability', 'array')->shouldBeCalled()->willReturn([
            'healthy' => $apiHealthy,
            'errors' => $apiHealthy ? '' : 'api_errors',
        ]);

        // sirius mock
        $this->injectProphecyService(SiriusApiGatewayClient::class, function ($client) use ($siriusHealthy) {
            $response = self::prophesize(PsrResponseInterface::class);
            if ($siriusHealthy) {
                $response->getStatusCode()->shouldBeCalled()->willReturn(200);
            } else {
                $response->getStatusCode()->shouldBeCalled()->willThrow(new \RuntimeException('sirius_error'));
            }

            $client->get('healthcheck')->shouldBeCalled()->willReturn($response->reveal());
        });

        // notify mock
        $this->injectProphecyService(NotifyAvailability::class, function ($availability) use ($notifyHealthy) {
            $availability->getName()->shouldBeCalled()->willReturn();
            $availability->isHealthy()->shouldBeCalled()->willReturn($notifyHealthy);
            $availability->getCustomMessage()->willReturn('');

            if (!$notifyHealthy) {
                $availability->getErrors()->shouldBeCalled()->willReturn('invalid key');
            }
        });

        // pdf mock
        $this->injectProphecyService(WkHtmlToPdfGenerator::class, function ($generator) use ($wkhtmltopdfError) {
            $generator->isAlive()->shouldBeCalled()->willReturn($wkhtmltopdfError);
        });

        // clamAV mock
        $response = m::mock(ResponseInterface::class)
                    ->shouldReceive('getStatusCode')->andReturn($clamReturnCode)->atLeast(1)->getMock();
        $guzzleMock = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('get')->andReturn($response)->getMock();
            //->getStatusCode')->andReturn(200);
        $container->set('guzzle_file_scanner_client', $guzzleMock);

        // dispatch /manage/availability and status code and check response
        $this->client->request('GET', '/manage/availability');
        $response = $this->client->getResponse();

        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        foreach ($mustContain as $m) {
            $this->assertStringContainsString($m, $response->getContent());
        }
    }

    public function testElb()
    {
        $this->client->request('GET', '/manage/elb');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('OK', $response->getContent());
    }

    public function tearDown(): void
    {
        m::close();
    }
}
