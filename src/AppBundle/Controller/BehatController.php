<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/behat")
 */
class BehatController extends AbstractController
{
    private function securityChecks()
    {
        if (!$this->container->getParameter('behat_controller_enabled')) {
            return $this->createNotFoundException('Behat endpoint disabled, check the behat_controller_enabled parameter');
        }

        $expectedSecretParam = md5('behat-dd-'.$this->container->getParameter('secret'));
        $secret = $this->getRequest()->get('secret');

        if ($secret !== $expectedSecretParam) {

            // log access
            $this->get('logger')->error($this->getRequest()->getPathInfo().": $expectedSecretParam secret expected. 404 will be returned.");

            throw $this->createNotFoundException('Not found');
        }
    }

    /**
     * @Route("/{secret}/email-get-last")
     * @Method({"GET"})
     */
    public function getLastEmailAction()
    {
        $this->securityChecks();
        
        $mailPath = $this->getBehatMailFilePath();

        if (!file_exists($mailPath)) {
            throw new \RuntimeException("Mail log $mailPath not existing.");
        }

        if (!is_readable($mailPath)) {
            throw new \RuntimeException("Mail log $mailPath unreadable.");
        }

        echo file_get_contents($mailPath);die;
        
        return new Response(file_get_contents($mailPath));
    }

    
    private function getBehatMailFilePath()
    {
        $this->securityChecks();

        return $this->container->getParameter('email_mock_path');
    }
    
    /**
     * @Route("/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function resetAction()
    {
        $this->securityChecks();
      
        
        $mailPath = $this->getBehatMailFilePath();

        file_put_contents($mailPath, '');
        

        return new Response('Email reset successfully');
    }
    

    /**
     * @Route("/{secret}/report/{reportId}/change-report-cot/{cotId}")
     * @Method({"GET"})
     */
    public function reportChangeReportCot($reportId, $cotId)
    {
        $this->securityChecks();

        $this->getRestClient()->put('behat/report/'.$reportId, [
            'cotId' => $cotId,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/{secret}/report/{reportId}/set-sumbmitted/{value}")
     * @Method({"GET"})
     */
    public function reportChangeSubmitted($reportId, $value)
    {
        $this->securityChecks();

        $submitted = ($value == 'true' || $value == 1) ? 1 : 0;

        $this->getRestClient()->put('behat/report/'.$reportId, [
            'submitted' => $submitted,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/{secret}/report/{reportId}/change-report-end-date/{dateYmd}")
     * @Method({"GET"})
     */
    public function accountChangeReportDate($reportId, $dateYmd)
    {
        $this->securityChecks();

        $this->getRestClient()->put('behat/report/'.$reportId, [
            'end_date' => $dateYmd,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/{secret}/delete-behat-users")
     * @Method({"GET"})
     */
    public function deleteBehatUser()
    {
        $this->securityChecks();

        $this->getRestClient()->delete('behat/users/behat-users');

        return new Response('done');
    }

    /**
     * @Route("/{secret}/delete-behat-data")
     * @Method({"GET"})
     */
    public function resetBehatData()
    {
        $this->securityChecks();

        return new Response('done');
    }

    /**
     * @Route("/{secret}/view-audit-log")
     * @Method({"GET"})
     * @Template()
     */
    public function viewAuditLogAction()
    {
        $this->securityChecks();

        $entities = $this->getRestClient()->get('behat/audit-log', 'AuditLogEntry[]');

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
     * set token_date and registration_token on the user.
     * 
     * @Route("/{secret}/user/{email}/token/{token}/token-date/{date}")
     * @Method({"GET"})
     */
    public function userSetToken($email, $token, $date)
    {
        $this->securityChecks();

        $this->getRestClient()->put('behat/user/'.$email, [
            'token_date' => $date,
            'registration_token' => $token,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/{secret}/check-app-params")
     * @Method({"GET"})
     */
    public function checkParamsAction()
    {
        $this->securityChecks();

        $data = $this->getRestClient()->get('behat/check-app-params', 'array');

        if ($data != 'valid') {
            throw new \RuntimeException('Invalid API params. Response: '.print_r($data, 1));
        }

        return new Response($data);
    }
}
