<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/email")
 */
class EmailController extends RestController
{
    /**
     * @Route("/send")
     * @Method({"POST"})
     * "", ""
     * 
      curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"toEmail":"elvisciotti@gmail.com", "toName":"Elvis", "fromEmail":"admin@digideps.service.dsd.io", "fromName":"Digital deputyship service", "subject": "subject!", "bodyText":"PLAIN BODY", "bodyHtml":"HTML <b>BODY</b>"}'  http://digideps-api.local/email/send
     */
    public function sendEmail()
    {
        $data = $this->deserializeBodyContent();
        
        array_map(function($k) use ($data) {
            if (!array_key_exists($k, $data)) {
                throw new \InvalidArgumentException("Missing parameter $k");
            }
        }, ['toEmail', 'toName', 'fromEmail', 'fromName', 'subject', 'bodyText', 'bodyHtml']);
        
        $mailerService = $this->container->get('mailer.service'); /* @var $mailerService \Swift_Mailer */
        
        $message = $mailerService->createMessage(); /* @var $message \Swift_Message */
        $message->setTo($data['toEmail'], $data['toName']);
        $message->setFrom($data['fromEmail'], $data['fromName']);
        
        $message->setSubject($data['subject']);
        $message->setBody($data['bodyText']);
        $message->addPart($data['bodyHtml'], 'text/html');
        
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $message->attach(new \Swift_Attachment($attachment['content'], $attachment['filename'], $attachment['contentType']));
            }
        }
        
        $result = $mailerService->send($message);
        
        return array('result'=>$result);
    }
}
