<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppException;

/**
 * @Route("/feedback")
 */
class FeedbackController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function sendFeedback(Request $request)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new AppException\UnauthorisedException('client secret not accepted.');
        }
        
        $feedbackData = $this->deserializeBodyContent($request);
       
        $feedbackEmail = $this->getMailFactory()->createFeedbackEmail($feedbackData);
        
        $ret = $this->get('mailSender')->send($feedbackEmail,[ 'html']);
        
        return $ret;
    }
}