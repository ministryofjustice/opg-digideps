<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class BehatController extends AbstractController
{
    private function securityChecks()
    {
        if (!$this->container->getParameter('behat_controller_enabled')) {
            return $this->createNotFoundException('Behat endpoint disabled, check the behat_controller_enabled parameter');
        }

        $expectedSecretParam = md5('behat-dd-' . $this->container->getParameter('secret'));
        $secret = $this->getRequest()->get('secret');

        if ($secret !== $expectedSecretParam) {

            // log access
            $this->get('logger')->error($this->getRequest()->getPathInfo() . ": $expectedSecretParam secret expected. 404 will be returned.");

            throw $this->createNotFoundException('Not found');
        }
    }

    /**
     * @Route("/behat/{secret}/email-get-last")
     * @Method({"GET"})
     */
    public function getLastEmailAction()
    {
        $this->securityChecks();

        echo $this->get('mail_sender')->getMockedEmailsRaw();
        die; //TODO check if works with response
    }

    /**
     * @Route("/behat/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function emailResetAction()
    {
        $this->securityChecks();

        $this->get('mail_sender')->resetMockedEmails();
        return new Response('Email reset successfully');
    }

    /**
     * @Route("/behat/{secret}/report/{reportId}/change-report-type/{type}")
     * @Method({"GET"})
     */
    public function reportChangeReportType($reportId, $type)
    {
        $this->securityChecks();

        $this->getRestClient()->put('behat/report/' . $reportId, [
            'type' => $type,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/behat/{secret}/report/{reportId}/change-report-end-date/{dateYmd}")
     * @Method({"GET"})
     */
    public function accountChangeReportDate($reportId, $dateYmd)
    {
        $this->securityChecks();

        $this->getRestClient()->put('behat/report/' . $reportId, [
            'end_date' => $dateYmd,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/behat/{secret}/delete-behat-users")
     * @Method({"GET"})
     */
    public function deleteBehatUser()
    {
        $this->securityChecks();

        $this->getRestClient()->delete('behat/users/behat-users');

        return new Response('done');
    }

    /**
     * @Route("/behat/{secret}/delete-behat-data")
     * @Method({"GET"})
     */
    public function resetBehatData()
    {
        $this->securityChecks();

        return new Response('done');
    }

    /**
     * @Route("/behat/{secret}/view-audit-log")
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
     * set token_date and registration_token on the user.
     *
     * @Route("/behat/{secret}/user/{email}/token/{token}/token-date/{date}")
     * @Method({"GET"})
     */
    public function userSetToken($email, $token, $date)
    {
        $this->securityChecks();

        $this->getRestClient()->put('behat/user/' . $email, [
            'token_date' => $date,
            'registration_token' => $token,
        ]);

        return new Response('done');
    }

    /**
     * @Route("/behat/{secret}/check-app-params")
     * @Method({"GET"})
     */
    public function checkParamsAction()
    {
        $this->securityChecks();

        $data = $this->getRestClient()->get('behat/check-app-params', 'array');

        if ($data != 'valid') {
            throw new \RuntimeException('Invalid API params. Response: ' . print_r($data, 1));
        }

        return new Response($data);
    }

    /**
     * Display emails into a webpage
     * Login is required
     *
     * @Route("/email-viewer/{action}", name="email-viewer")
     * @Template()
     */
    public function emailViewerAction($action)
    {
        $emailToView = 'AppBundle:Email:' . $action . '.html.twig';

        return $this->render($emailToView, [
            'homepageUrl' => 'https://complete-deputy-report.service.gov.uk/',
            'domain' => 'https://complete-deputy-report.service.gov.uk/',
            'deputyFirstName' => 'Peter White',
            'pdfLink' => '#',
            'link' => 'https://complete-deputy-report.service.gov.uk/',
            'submittedReport' => new Report(),
            'newReport' => new Report(),
            'response' => [
                'satisfactionLevel' => 'Satisfied',
            ],
        ]);
    }
}
