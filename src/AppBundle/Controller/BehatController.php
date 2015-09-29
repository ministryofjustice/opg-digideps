<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
    public function emailAction()
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
     * @Route("/report/{reportId}")
     * @Method({"PUT"})
     */
    public function reportChangeCotAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy('Report', $reportId);
        
        $data = $this->deserializeBodyContent($request);
        
        if (!empty($data['cotId'])) {
            $cot = $this->findEntityBy('CourtOrderType', $data['cotId']);
            $report->setCourtOrderType($cot);
        }
        
        if (array_key_exists('submitted', $data)) {
            $report->setSubmitted($data['submitted']);
            $report->setSubmitDate($data['submitted'] ? new \DateTime : null);
        }
        
        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
        }
        
        $this->get('em')->flush($report);
        
        return true;
    }
    
    
    
    /**
     * @Route("/check-app-params")
     * @Method({"GET"})
     */
    public function checkParamsAction()
    {
        $param = $this->container->getParameter('email_report_submit')['to_email'];
        if (!preg_match('/^behat\-/', $param)) {
            throw new DisplayableException("email_report_submit.to_email must be a behat- email in order to test emails, $param given.");
        }
        
        $param = $this->container->getParameter('email_feedback_send')['to_email'];
        if (!preg_match('/^behat\-/', $param)) {
            throw new DisplayableException("email_feedback_send.to_email must be a behat- email in order to test emails, $param given.");
        }
        
        return "valid";
    }
    
    /**
     * @Route("/audit-log")
     * @Method({"GET"})
     */
    public function auditLogGetAllAction()
    {
        $this->setJmsSerialiserGroups(['audit_log']);

        return $this->getRepository('AuditLogEntry')->findBy([], ['id'=>'DESC']);
    }
    
     /**
     * @Route("/user/{email}")
     * @Method({"PUT"})
     */
    public function editUser(Request $request, $email)
    {
        $data = $this->deserializeBodyContent($request);
        $user = $this->getRepository('User')->findBy(['email'=>$email]);
        
        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }
        
        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }
        
        return "done";
    }
    
    /**
     * @Route("/email")
     * @Method({"DELETE"})
     */
    public function emailResetAction()
    {
        $mailPath = $this->getBehatMailFilePath();
        
        file_put_contents($mailPath, '');
        
        return "Email reset successfully";
    }
    
    /**
     * @Route("/behat-data")
     * @Method({"DELETE"})
     */
//    public function deleteBehatDataAction()    {
//        $em = $this->getEntityManager();
//        
//        foreach ($this->getRepository('User')->findAll() as $user) {  /* @var $user User */
//            if (preg_match('/^behat-/', $user->getEmail())) {
//                foreach ($user->getClients() as $client) {
//                    foreach ($client->getReports() as $report) {
//                       $em->remove($report);
//                    }
//                    $em->remove($client);
//                }
//                $em->remove($user);
//            }
//        }
//        
//        return "User deleted";
//    }
    
    private function getBehatMailFilePath()
    {
        return $this->container->getParameter('email_mock_path');
    }
    
    
    
}
