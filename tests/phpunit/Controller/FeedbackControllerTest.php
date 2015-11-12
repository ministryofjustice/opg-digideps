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
    
    public function testsendFeedbackMissingClientSecre()
    {
        $url = '/feedback';
        
        $this->assertJsonRequest('POST', $url, [
            'mustFail' => true,
            'assertResponseCode' => 403
        ]);
    }
    
    public function testsendFeedback()
    {
        $url = '/feedback';
        MailSenderMock::resetessagesSent();
        
        $this->assertJsonRequest('POST', $url, [
            'mustSucceed'=>true,
            'ClientSecret' => '123abc-deputy',
            'data'=>[
                'difficulty' => 'difficulty-response'
            ]
        ])['data'];
        
        $this->assertCount(1, MailSenderMock::getMessagesSent()['mailer.transport.smtp.default']);
        $email = MailSenderMock::getMessagesSent()['mailer.transport.smtp.default'][0];
        $this->assertEquals('User Feedback', $email['subject']);
        $this->assertContains('difficulty-response', $email['parts'][0]['body']);
    }
}
