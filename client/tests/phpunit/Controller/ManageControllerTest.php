<?php

namespace AppBundle\Controller;

use GuzzleHttp\Message\ResponseInterface;
use Mockery as m;

class ManageControllerTest extends AbstractControllerTestCase
{
    /** @var ManageController */
    protected $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->sut = new ManageController();
    }

    public static function availabilityProvider()
    {
        return [
            [true, true,  true,  true,  true,  200, 200, ['OK']], //all good
            [false, true, true,  true,  true, 200,  500, ['redis-error']],
            [true, false, true,  true,  true, 200,  500, ['api_errors']],
            [true, true,  true,  true,  false, 200, 500, ['wkhtmltopdf.isAlive']],
            [true, true,  true,  true,  false, 500, 500, ['returned HTTP']],
        ];
    }

    public function getRouteMap()
    {
        return [
            ['/manage/availability', 'availabilityAction'],
            ['/manage/elb', 'elbAction'],
        ];
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailability(
        $redisHealthy, $apiHealthy, $smtpDefault, $smtpSecure, $wkhtmltopdfError, $clamReturnCode,
        $statusCode, array $mustContain)
    {
        //redis mock
        $redisMock = m::mock('Predis\Client');
        if ($redisHealthy) {
            $redisMock->shouldReceive('set')->with('RedisAvailabilityTestKey', 'valueSaved');
            $redisMock->shouldReceive('get')->with('RedisAvailabilityTestKey')->andReturn('valueSaved');
        } else {
            $redisMock->shouldReceive('set')->andThrow(new \RuntimeException('redis-error'));
        }
        $this->container->set('snc_redis.default', $redisMock);

        // api mock
        $restClient = m::mock('AppBundle\Service\Client\RestClient');
        $restClient->shouldReceive('get')->with('manage/availability', 'array')->andReturn([
            'healthy' => $apiHealthy,
            'errors' => $apiHealthy ? '' : 'api_errors',
        ]);
        $this->container->set('rest_client', $restClient);

        // smtp mock
        $smtpMock = m::mock('Swift_Transport');
        if ($smtpDefault) {
            $smtpMock->shouldReceive('start')->atLeast(1)->shouldReceive('stop')->atLeast(1);
        } else {
            $smtpMock->shouldReceive('start')->andThrow(new \RuntimeException('sd-error'));
        }
        $this->container->set('mailer.transport.smtp.default', $smtpMock);

        // smtp secure mock
        $secureSmtpMock = m::mock('Swift_Transport');
        if ($smtpSecure) {
            $secureSmtpMock->shouldReceive('start')->atLeast(1)->shouldReceive('stop')->atLeast(1);
        } else {
            $secureSmtpMock->shouldReceive('start')->andThrow(new \RuntimeException('ss-error'));
        }
        $this->container->set('mailer.transport.smtp.default', $secureSmtpMock);

        // pdf mock
        $wkhtmltopdfErrorMock = m::mock('AppBundle\Service\WkHtmlToPdfGenerator')
            ->shouldReceive('isAlive')->andReturn($wkhtmltopdfError)
        ->getMock();
        $this->container->set('wkhtmltopdf', $wkhtmltopdfErrorMock);

        // clamAV mock
        $response = m::mock(ResponseInterface::class)
                    ->shouldReceive('getStatusCode')->andReturn($clamReturnCode)->atLeast(1)->getMock();
        $guzzleMock = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('get')->andReturn($response)->getMock();
            //->getStatusCode')->andReturn(200);
        $this->container->set('guzzle_file_scanner_client', $guzzleMock);

        $this->sut->setContainer($this->container);

        // dispatch /manage/availability and status code and check response
        $response = $this->sut->availabilityAction();

        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        foreach ($mustContain as $m) {
            $this->assertStringContainsString($m, $response->getContent());
        }
    }

    public function testElb()
    {
        $response = $this->sut->elbAction();

        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('OK', $response['status']);
    }

    public function tearDown(): void
    {
        m::close();
    }
}
