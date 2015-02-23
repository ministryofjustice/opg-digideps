<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Client;

/**
 * @Route("/behat")
 */
class BehatController extends RestController
{
    /**
     * @Route("/email")
     * @Method({"GET"})
     */
    public function getAction()
    {
        $mailPath = $this->getBehatMailFilePath();
        
        return file_get_contents($mailPath);
    }
    
    /**
     * @Route("/email")
     * @Method({"DELETE"})
     */
    public function resetAction()
    {
        $mailPath = $this->getBehatMailFilePath();
        
        file_put_contents($mailPath, '');
        
        return "Email reset successfully";
    }
    
    private function getBehatMailFilePath()
    {
        return current($this->get('mailer.transport.sendgrid')->getEmailFileWriters());
    }
}
