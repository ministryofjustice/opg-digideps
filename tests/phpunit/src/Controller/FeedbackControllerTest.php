<?php

namespace AppBundle\Controller;

use AppBundle\Service\Mailer\MailSenderMock;

class FeedbackControllerTest extends AbstractTestController
{
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testsendFeedbackHomepageMissingClientSecre()
    {
        $url = '/feedback/homepage';

        $this->assertJsonRequest('POST', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403,
        ]);
    }

    public function testsendFeedbackHomepage()
    {
        $url = '/feedback/homepage';
        MailSenderMock::resetessagesSent();

        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'ClientSecret' => '123abc-deputy',
            'data' => [
                'difficulty' => 'difficulty-response',
            ],
        ])['data'];

        $this->assertCount(1, MailSenderMock::getMessagesSent()['mailer.transport.smtp.default']);
        $email = MailSenderMock::getMessagesSent()['mailer.transport.smtp.default'][0];
        $this->assertEquals('User Feedback', $email['subject']);
        $this->assertContains('difficulty-response', base64_decode($email['parts'][0]['body']));
    }

    public function testsendFeedbackReportAuth()
    {
        $url = '/feedback/report';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);
    }

    public function testsendFeedbackReport()
    {
        $url = '/feedback/report';
        MailSenderMock::resetessagesSent();

        $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [

            ],
        ])['data'];

        $this->assertCount(1, MailSenderMock::getMessagesSent()['mailer.transport.smtp.default']);
        $this->assertEquals('User Feedback', MailSenderMock::getMessagesSent()['mailer.transport.smtp.default'][0]['subject']);
    }
}
