<?php

namespace AppBundle\Controller;

use Mockery as m;

class ManageControllerTest extends AbstractControllerTestCase
{
    public static function availabilityProvider()
    {
        return [
            [true, true,  true,  true,  true,  200, ['OK']], //all good
            [false, true, true,  true,  true,  500, ['redis-error']],
            [true, false, true,  true,  true,  500, ['api_errors']],
            [true, true,  false, true,  true,  500, ['sd-error']],
            [true, true,  true,  false, true,  500, ['ss-error']],
            [true, true,  true,  true,  false, 500, ['wkhtmltopdf.isAlive']],
                // all down
            [true, false, false, false, false, 500, ['api_errors', 'sd-error', 'ss-error', 'wkhtmltopdf.isAlive']],
        ];
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailability(
        $redisHealthy, $apiHealthy, $smtpDefault, $smtpSecure, $wkhtmltopdfError,
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

        // dispatch /manage/availability and status code and check response
        $response = $this->httpRequest('GET', '/manage/availability');
        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        foreach ($mustContain as $m) {
            $this->assertContains($m, $response->getContent());
        }
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailabilityPingdom(
        $redisHealthy, $apiHealthy, $smtpDefault, $smtpSecure, $wkhtmltopdfError,
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

        // dispatch /manage/availability and status code and check response
        $response = $this->httpRequest('GET', '/manage/availability/pingdom');
        $this->assertEquals($statusCode, $response->getStatusCode(), $response->getContent());
        // XML doesn't return details of errors
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
