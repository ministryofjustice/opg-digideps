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
     * @Route("/users/behat-users")
     * @Method({"DELETE"})
     */
    public function usersBehatDeleteAction()
    {
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
//        $this->getEntityManager()
//            ->createQuery("DELETE FROM AppBundle\Entity\User u WHERE u.email LIKE 'behat-%'")
//            ->execute();
        
        //TODO fix cascade delete
        
        return "User deleted";
    }
    
    private function getBehatMailFilePath()
    {
        return current($this->get('mailer.transport.sendgrid')->getEmailFileWriters());
    }
}
