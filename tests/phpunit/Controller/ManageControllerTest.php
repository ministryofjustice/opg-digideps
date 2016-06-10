<?php

namespace AppBundle\Controller;

use Mockery as m;

class ManageControllerTest extends AbstractControllerTestCase
{
    public static function availabilityProvider()
    {
        return [
            [true, '', true, true, true, 200, ['OK']], //all good
            [false, 'db offline', true, true, true, 500, ['db offline']],
            [true, '', false, true, true, 500, ['smtpDefault offline']],
            [true, '', true, false, true, 500, ['smtpSecure offline']],
            [true, '', true, true, false, 500, ['wkhtmltopdf']],
                // all down
            [false, 'db offline', false, false, false, 500, ['db offline', 'smtpDefault offline', 'smtpSecure offline', 'wkhtmltopdf']],
        ];
    }

    /**
     * @dataProvider availabilityProvider
     */
    public function testAvailability(
        $apiHealthy, $apiErrors, $smtpDefault, $smtpSecure, $wkhtmltopdf,
        $statusCode, array $mustContain)
    {
        $container = $this->frameworkBundleClient->getContainer();


        // api mock
        $this->restClient->shouldReceive('get')->with('manage/availability', 'array')->andReturn([
            'healthy' => $apiHealthy,
            'errors' => $apiErrors,
        ]);

        // smtp mock
        $smtpMock = m::mock('Swift_Transport');
        if ($smtpDefault) {
            $smtpMock->shouldReceive('start')->times(1)->shouldReceive('stop')->times(1);
        } else {
            $smtpMock->shouldReceive('start')->andThrow(new \RuntimeException('smtpDefault offline'));
        }
        $container->set('mailer.transport.smtp.default', $smtpMock);

        // smtp secure mock
        $secureSmtpMock = m::mock('Swift_Transport');
        if ($smtpSecure) {
            $secureSmtpMock->shouldReceive('start')->times(1)->shouldReceive('stop')->times(1);
        } else {
            $secureSmtpMock->shouldReceive('start')->andThrow(new \RuntimeException('smtpSecure offline'));
        }
        $container->set('mailer.transport.smtp.secure', $secureSmtpMock);

        // pdf mock
        $wkhtmltopdfMock = m::mock('AppBundle\Service\WkHtmlToPdfGenerator')
            ->shouldReceive('isAlive')->andReturn($wkhtmltopdf)
        ->getMock();
        $container->set('wkhtmltopdf', $wkhtmltopdfMock);

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
