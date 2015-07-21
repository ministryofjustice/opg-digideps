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
        $content = $this->get('apiclient')->get('behat/email')->getBody();
        
        $contentArray = json_decode($content, 1);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new Response("Error decoding email: body:" . $content);
        }
        
        return new Response($contentArray['data']);
    }
    
    /**
     * @Route("/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function resetAction()
    {
        $this->checkIsBehatBrowser();
        $content = $this->get('apiclient')->delete('behat/email')->getBody();
        
        return new Response($content);
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-cot/{cot}")
     * @Method({"GET"})
     */
    public function reportChangeReportCot($reportId, $cot)
    {
        $this->checkIsBehatBrowser();
        $this->get('apiclient')->putC('report/'  .$reportId, json_encode([
            'cot_id' => $cot
        ]));
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/set-sumbmitted/{value}")
     * @Method({"GET"})
     */
    public function reportChangeSubmitted($reportId, $value)
    {
        $this->checkIsBehatBrowser();
        $this->get('apiclient')->putC('report/'  .$reportId, json_encode([
            'submitted' => ($value == 'true' || $value == 1)
        ]));
        
        return new Response('done');
    }
    
    
    /**
     * @Route("/{secret}/delete-behat-users")
     * @Method({"GET"})
     */
    public function deleteBehatUser()
    {
        $this->checkIsBehatBrowser();
        
        $this->get('apiclient')->delete('behat/users/behat-users');
        
        return new Response('done');
    }
    
    /**
     * @Route("/{secret}/report/{reportId}/change-report-end-date/{dateYmd}")
     * @Method({"GET"})
     */
    public function accountChangeReportDate($reportId, $dateYmd)
    {
        $this->get('apiclient')->putC('report/' . $reportId, json_encode([
            'end_date' => $dateYmd
        ]));
        
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
        
        $entities = $this->get('apiclient')->getEntities('AuditLogEntry', 'audit-log');
        
        return ['entries' => $entities];
    }
    
    /**
     * @Route("/oauth-check-pass")
     */
    public function checkOAuth2PassAction()
    {
        if($this->container->getParameter('oauth2_enabled')){
            $oauth2Client = $this->get('oauth2client');
            $oauth2Client->setUserCredentials('behat-user@publicguardian.gsi.gov.uk','9k4PZrYAhWIMcVCELlGk/xJmzYtFLGmta924lBP/VvM4T7sfEDomfn373dueeyh+CADl/aPlzOQV0h+3h1N3Wg==');

            $config = [ 'base_url' =>  $this->container->getParameter('api_base_url'),
                        'defaults' => ['headers' => [ 'Content-Type' => 'application/json'],
                                       'verify' => false,
                                       'auth' => 'oauth2',
                                       'subscribers' => [ $oauth2Client->getSubscriber() ]
                                       ]];

            $guzzleClient = new Client($config);
            $response = $guzzleClient->get('report/find-by-id/1');

            return new JsonResponse($response->json());
        }
        return new JsonResponse();
    }
    
    /**
     * @Route("/oauth-check-fail")
     */
    public function checkOAuth2FailAction()
    {
        if($this->container->getParameter('oauth2_enabled')){
            $oauth2Client = $this->get('oauth2client');
            $oauth2Client->setUserCredentials('wrong-email@publicguardian.gsi.gov.uk','9k4PZrYAhWIMcVCELlGk/xJmzYtFLGmta924lBP/VvM4T7sfEDomfn373dueeyh+CADl/aPlzOQV0h+3h1N3Wg==');

            $config = [ 'base_url' =>  $this->container->getParameter('api_base_url'),
                        'defaults' => ['headers' => [ 'Content-Type' => 'application/json'],
                                       'verify' => false,
                                       'auth' => 'oauth2',
                                       'subscribers' => [ $oauth2Client->getSubscriber() ]
                                       ]];

            $guzzleClient = new Client($config);
            $response = $guzzleClient->get('report/find-by-id/1');

            return new JsonResponse($response->json());
        }
        return new JsonResponse();
    }
    
    /**
     * @Route("/textarea")
     */
    public function textAreaTestPage()
    {
        return $this->render('AppBundle:Behat:textarea.html.twig');    
    }
    
    /**
     * @Route("/{secret}/user/{email}/token/{token}/token-date/{date}")
     * @Method({"GET"})
     */
    public function userSetToken($email, $token, $date)
    {
        $this->checkIsBehatBrowser();
        
        $user = $this->get('apiclient')->getEntity('User', 'find_user_by_email', [ 'parameters' => [ 'email' => $email ] ]);
        $user->setTokenDate(new \DateTime($date));
        $user->setRegistrationToken($token);
        
        $this->get('apiclient')->putC('user/' . $user->getId(), $user, [
            'deserialise_group' => 'registrationToken',
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
        
        $param = $this->container->getParameter('email_report_submit')['to_email'];
        if (!preg_match('/^behat\-/', $param)) {
            throw new DisplayableException("email_report_submit.to_email must be a behat- email in order to test emails, $param given.");
        }
        
        $param = $this->container->getParameter('email_feedback_send')['to_email'];
        if (!preg_match('/^behat\-/', $param)) {
            throw new DisplayableException("email_feedback_send.to_email must be a behat- email in order to test emails, $param given.");
        }
        
        return new Response('ok');
    }
}
