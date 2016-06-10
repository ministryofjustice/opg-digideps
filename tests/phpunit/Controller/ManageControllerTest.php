<?php

namespace AppBundle\Controller;

use Mockery as m;

class ManageControllerTest extends AbstractControllerTestCase
{
    public static function availabilityProvider()
    {
        return [
            [true,  true,  true,  true,  200, ['OK']], //all good
            [false, true,  true,  true,  500, ['api_errors']],
            [true,  false, true,  true,  500, ['sd-error']],
            [true,  true,  false, true,  500, ['ss-error']],
            [true,  true,  true,  false, 500, ['wkhtmltopdf.isAlive']],
                // all down
            [false, false, false, false, 500, ['api_errors', 'sd-error', 'ss-error', 'wkhtmltopdf.isAlive']],
        ];
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailability(
        $apiHealthy, $smtpDefault, $smtpSecure, $wkhtmltopdfError,
        $statusCode, array $mustContain)
    {
        $container = $this->frameworkBundleClient->getContainer();

        // api mock
        $this->restClient->shouldReceive('get')->with('manage/availability', 'array')->andReturn([
            'healthy' => $apiHealthy,
            'errors' => $apiHealthy ? '' : 'api_errors',
        ]);

        // smtp mock
        $smtpMock = m::mock('Swift_Transport');
        if ($smtpDefault) {
            $smtpMock->shouldReceive('start')->times(1)->shouldReceive('stop')->times(1);
        } else {
            $smtpMock->shouldReceive('start')->andThrow(new \RuntimeException('sd-error'));
        }
        $container->set('mailer.transport.smtp.default', $smtpMock);

        // smtp secure mock
        $secureSmtpMock = m::mock('Swift_Transport');
        if ($smtpSecure) {
            $secureSmtpMock->shouldReceive('start')->times(1)->shouldReceive('stop')->times(1);
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
        $this->assertEquals($statusCode, $response->getStatusCode());
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
