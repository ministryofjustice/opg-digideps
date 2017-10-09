<?php

namespace AppBundle\Controller;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use Mockery as m;

class ManageControllerTest extends AbstractControllerTestCase
{
    public static function availabilityProvider()
    {
        return [
            [true, true,  true,  true,  true,  200, 200, ['OK']], //all good
            [false, true, true,  true,  true, 200,  500, ['redis-error']],
            [true, false, true,  true,  true, 200,  500, ['api_errors']],
            [true, true,  false, true,  true, 200,  500, ['sd-error']],
            [true, true,  true,  false, true, 200,  500, ['ss-error']],
            [true, true,  true,  true,  false, 200, 500, ['wkhtmltopdf.isAlive']],
            [true, true,  true,  true,  false, 500, 500, ['returned HTTP']],
        ];
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailability(
        $redisHealthy, $apiHealthy, $smtpDefault, $smtpSecure, $wkhtmltopdfError, $clamReturnCode,
        $statusCode, array $mustContain)
    {
        $container = $this->frameworkBundleClient->getContainer();

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
        $this->restClient->shouldReceive('get')->with('manage/availability', 'array')->andReturn([
            'healthy' => $apiHealthy,
            'errors' => $apiHealthy ? '' : 'api_errors',
        ]);

        // smtp mock
        $smtpMock = m::mock('Swift_Transport');
        if ($smtpDefault) {
            $smtpMock->shouldReceive('start')->atLeast(1)->shouldReceive('stop')->atLeast(1);
        } else {
            $smtpMock->shouldReceive('start')->andThrow(new \RuntimeException('sd-error'));
        }
        $container->set('mailer.transport.smtp.default', $smtpMock);

        // smtp secure mock
        $secureSmtpMock = m::mock('Swift_Transport');
        if ($smtpSecure) {
            $secureSmtpMock->shouldReceive('start')->atLeast(1)->shouldReceive('stop')->atLeast(1);
        } else {
            $secureSmtpMock->shouldReceive('start')->andThrow(new \RuntimeException('ss-error'));
        }
        $container->set('mailer.transport.smtp.secure', $secureSmtpMock);

        // pdf mock
        $wkhtmltopdfErrorMock = m::mock('AppBundle\Service\WkHtmlToPdfGenerator')
            ->shouldReceive('isAlive')->andReturn($wkhtmltopdfError)
        ->getMock();
        $container->set('wkhtmltopdf', $wkhtmltopdfErrorMock);

        // clamAV mock
        $response = m::mock(ResponseInterface::class)
                    ->shouldReceive('getStatusCode')->andReturn($clamReturnCode)->atLeast(1)->getMock();
        $guzzleMock = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('get')->andReturn($response)->getMock();
            //->getStatusCode')->andReturn(200);
        $container->set('guzzle_file_scanner_client', $guzzleMock);

        // dispatch /manage/availability and status code and check response
        $response = $this->httpRequest('GET', '/manage/availability');
        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        foreach ($mustContain as $m) {
            $this->assertContains($m, $response->getContent());
        }
    }

    public function testElb()
    {
        $response = $this->httpRequest('GET', '/manage/elb');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('OK', $response->getContent());
    }

    public function tearDown()
    {
        m::close();
    }
}
