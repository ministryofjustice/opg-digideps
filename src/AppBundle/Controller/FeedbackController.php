<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/feedback")
 */
class FeedbackController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function sendFeedback()
    {
        $feedbackData = $this->deserializeBodyContent();
       
        $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($feedbackData);
        
        $ret = $this->get('mailSender')->send($feedbackEmail,[ 'html']);
        
        return $ret;
    }
    
    /**
     * @Route("/report")
     * @Method({"POST"})
     */
    public function sendReportFeedback()
    {
       $feedbackData = $this->deserializeBodyContent();
         
       $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($feedbackData);
       $ret = $this->get('mailSender')->send($feedbackEmail, [ 'html']);
       
       return $ret;
    }
    
}