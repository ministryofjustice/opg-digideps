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
    
    public function testsendFeedback()
    {
        $url = '/feedback';
        MailSenderMock::resetessagesSent();
        
        $this->assertJsonRequest('POST', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
            'data'=>[
                
            ]
        ])['data'];
        
        $this->assertCount(1, MailSenderMock::getMessagesSent()['mailer.transport.smtp.default']);
        $this->assertEquals('User Feedback', MailSenderMock::getMessagesSent()['mailer.transport.smtp.default'][0]['subject']);
    }
}
