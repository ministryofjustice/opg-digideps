<?php
namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Exception\DisplayableException;

/**
 * @Route("/behat")
 */
class BehatController extends Controller
{
    private function checkIsBehatBrowser()
    {
        $expectedSecretParam = md5('behat-dd-' . $this->container->getParameter('secret'));
        $secret = $this->getRequest()->get('secret');
        
        if ($secret !== $expectedSecretParam) {
            
            // log access
            $this->get('logger')->error($this->getRequest()->getPathInfo(). ": $expectedSecretParam secret expected. 404 will be returned.");
            
            throw $this->createNotFoundException('Not found');
        }
    }
    
    /**
     * @Route("/{secret}/email-get-last")
     * @Method({"GET"})
     */
    public function getLastEmailAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('restClient')->get('behat/email', 'array');
        
        return new Response($content);
    }
    
    /**
     * @Route("/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function resetAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('restClient')->delete('behat/email');
        
        return new Response($content);
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-cot/{cotId}")
     * @Method({"GET"})
     */
    public function reportChangeReportCot($reportId, $cotId)
    {
        $this->checkIsBehatBrowser();
        
        $this->get('restClient')->put('behat/report/' .$reportId, [
            'cotId' => $cotId
        ]);
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/set-sumbmitted/{value}")
     * @Method({"GET"})
     */
    public function reportChangeSubmitted($reportId, $value)
    {
        $this->checkIsBehatBrowser();
        
        $submitted = ($value == 'true' || $value == 1) ? 1 : 0;
        
        $this->get('restClient')->put('behat/report/' .$reportId, [
            'submitted' => $submitted
        ]);
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-end-date/{dateYmd}")
     * @Method({"GET"})
     */
    public function accountChangeReportDate($reportId, $dateYmd)
    {
        $this->get('restClient')->put('behat/report/' . $reportId, [
            'end_date' => $dateYmd
        ]);
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/delete-behat-users")
     * @Method({"GET"})
     */
    public function deleteBehatUser()
    {
        $this->checkIsBehatBrowser();
        
        $this->get('restClient')->delete('behat/users/behat-users');
        
        return new Response('done');
    }
    
    
    
    /**
     * @Route("/{secret}/delete-behat-data")
     * @Method({"GET"})
     */
    public function resetBehatData()
    {
       return new Response('done');
    }
    
    /**
     * @Route("/{secret}/view-audit-log")
     * @Method({"GET"})
     * @Template()
     */
    public function viewAuditLogAction()
    {
        $this->checkIsBehatBrowser();
        
        $entities = $this->get('restClient')->get('behat/audit-log', 'AuditLogEntry[]');
   
        return ['entries' => $entities];
    }
    
    /**
     * @Route("/textarea")
     */
    public function textAreaTestPage()
    {
        return $this->render('AppBundle:Behat:textarea.html.twig');    
    }
    
    /**
     * set token_date and registration_token on the user
     * 
     * @Route("/{secret}/user/{email}/token/{token}/token-date/{date}")
     * @Method({"GET"})
     */
    public function userSetToken($email, $token, $date)
    {
        $this->checkIsBehatBrowser();
        
        $this->get('restClient')->put('behat/user/'.$email, [
            'token_date' => $date,
            'registration_token' => $token
        ]);
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/check-app-params")
     * @Method({"GET"})
     */
    public function checkParamsAction()
    {
        $this->checkIsBehatBrowser();
        
        $data = $this->get('restClient')->get('behat/check-app-params', 'array');
        
        if ($data !='valid') {
            throw new \RuntimeException('Invalid API params. Response: '.print_r($data, 1));
        }
        
        return new Response($data);
    }
}
