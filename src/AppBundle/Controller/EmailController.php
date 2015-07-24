<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Mailer\Utils\MessageUtils;
use Swift_Mailer;
use Swift_Message;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/email")
 */
class EmailController extends RestController
{
    /**
     * @Route("/send/{transport}",defaults={ "transport" = "default"}, requirements={"(default|secure-smtp)"})
     * @Method({"POST"})
     * "", ""
     * 
      curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"toEmail":"elvisciotti@gmail.com", "toName":"Elvis", "fromEmail":"admin@digideps.service.dsd.io", "fromName":"Digital deputyship service", "subject": "subject!", "bodyText":"PLAIN BODY", "bodyHtml":"HTML <b>BODY</b>"}'  http://digideps-api.local/email/send
     */
    public function sendEmail($transport)
    {
        $data = $this->deserializeBodyContent();

        array_map(function($k) use ($data) {
            if (!array_key_exists($k, $data)) {
                throw new \InvalidArgumentException("Missing parameter $k");
            }
        }, ['toEmail', 'toName', 'fromEmail', 'fromName', 'subject', 'bodyText', 'bodyHtml']);

        $mailTransport = 'mailer.transport.smtp.default';
        if ($transport == 'secure-smtp') {
            $mailTransport = 'mailer.transport.smtp.secure';
        }

        $mailerService = new Swift_Mailer($this->container->get($mailTransport));
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

        // behat-@ emails goes to file instead of real sending
        if ($this->isEmailToMock($message)) {
            $result = $this->prependMessageIntoEmailMockPath($message);
        } else {
            $result = $mailerService->send($message);
        }

        return array('result' => $result);
    }

    /**
     * @param \Swift_Mime_Message $message
     * @return boolean
     */
    private function isEmailToMock(Swift_Message $message)
    {
        $addressToMockRegexp = $this->container->hasParameter('email_mock_address') ? $this->container->getParameter('email_mock_address') : null;
        $path = $this->container->hasParameter('email_mock_path') ? $this->container->getParameter('email_mock_path') : null;
        if (!$addressToMockRegexp || !$path) {
            return false;
        }

        // get "to"
        $to = $message->getTo();
        reset($to);
        $emailAddress = key($to);

        return preg_match($addressToMockRegexp, $emailAddress);
    }

    /**
     * @param \AppBundle\Controller\Swift_Mime_Message $swiftMessage
     * @return type
     * @throws \RuntimeException
     */
    private function prependMessageIntoEmailMockPath(Swift_Message $swiftMessage)
    {
        // prepend email into the file
        $emails =  $this->getEmailsFromFile();
        
        array_unshift($emails, MessageUtils::messageToArray($swiftMessage));
        
        $ret = $this->writeEmailsToFile($emails);

        return "Email saved. $ret bytes written.";
    }
    
    /**
     * @return array
     */
    private function getEmailsFromFile()
    {
        $path = $this->container->getParameter('email_mock_path');
        
        return json_decode(file_get_contents($path), true) ?: [];
    }
    
    /**
     * @return array
     */
    private function writeEmailsToFile(array $emails)
    {
        $path = $this->container->getParameter('email_mock_path');
        
        $ret = file_put_contents($path, json_encode($emails));
        
        if (false === $ret) {
            throw new \RuntimeException("Cannot write email into $path");
        }
        
        return $ret;
    }

}