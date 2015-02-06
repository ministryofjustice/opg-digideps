<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\JsonResponse;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\FOSRestController;
//use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        
        $mailerService = $this->container->get('mailer.service');
        
        $message = $mailerService->createMessage();
        $message->setTo($data['toEmail'], $data['toName']);
        $message->setFrom($data['fromEmail'], $data['fromName']);
        
        $message->setSubject($data['subject']);
        $message->setBody($data['bodyText']);
        $message->addPart($data['bodyHtml'], 'text/html');
        
        $result = $mailerService->send($message);
        
        return array('result'=>$result);
    }
}
