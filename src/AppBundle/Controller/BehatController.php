<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;

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
        
        if (!file_exists($mailPath)) {
            throw new \RuntimeException("Mail log $mailPath not existing.");
        }
        
        if (!is_readable($mailPath)) {
            throw new \RuntimeException("Mail log $mailPath unreadable.");
        }
        
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
    
    /**
     * @Route("/behat-data")
     * @Method({"DELETE"})
     */
    public function deleteBehatDataAction()    {
        $em = $this->getEntityManager();
        
        foreach ($this->getRepository('User')->findAll() as $user) {  /* @var $user User */
            if (preg_match('/^behat-/', $user->getEmail())) {
                foreach ($user->getClients() as $client) {
                    foreach ($client->getReports() as $report) {
                       $em->remove($report);
                    }
                    $em->remove($client);
                }
                $em->remove($user);
            }
        }
        
        return "User deleted";
    }
    
    private function getBehatMailFilePath()
    {
        return $this->container->getParameter('email_mock_path');
    }
}
