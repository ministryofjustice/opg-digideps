<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\Mailer\MailFactory;
use Monolog\Logger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BehatController extends AbstractController
{
    private function securityChecks(Request $request)
    {
        if (!$this->container->getParameter('behat_controller_enabled')) {
            return $this->createNotFoundException('Behat endpoint disabled, check the behat_controller_enabled parameter');
        }

        $expectedSecretParam = md5('behat-dd-' . $this->container->getParameter('secret'));
        $secret = $request->get('secret');

        if ($secret !== $expectedSecretParam) {

            // log access
            $this->get('logger')->error($request->getPathInfo() . ": $expectedSecretParam secret expected. 404 will be returned.");

            throw $this->createNotFoundException('Not found');
        }
    }

    /**
     * @Route("/behat/{secret}/email-get-last")
     * @Method({"GET"})
     */
    public function getLastEmailAction(Request $request)
    {
        $this->securityChecks($request);

        echo $this->get('mail_sender')->getMockedEmailsRaw();
        die; //TODO check if works with response
    }

    /**
     * @Route("/behat/{secret}/email-reset")
     * @Method({"GET"})
     */
    public function emailResetAction(Request $request)
    {
        $this->securityChecks($request);

        $this->get('mail_sender')->resetMockedEmails();
        return new Response('Email reset successfully');
    }

    /**
     * @Route("/behat/emails")
     * @Method({"GET"})
     * @Template
     */
    public function emailsAction(Request $request)
    {
        if ($this->get('kernel')->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $emails = json_decode($this->get('mail_sender')->getMockedEmailsRaw(), true);

        return [
            'emails' => $emails,
            'isAdmin' => $this->container->getParameter('env') === 'admin',
            'host' => $_SERVER['HTTP_HOST'],
        ];
    }

    /**
     * Display emails into a webpage
     * Login is required
     *
     * @Route("/email-viewer/{action}/{type}", name="email-viewer", defaults={"type"="html"})
     * @Template()
     */
    public function emailViewerAction($action, $type = 'html')
    {
        $type = $type === 'html' ? $type : 'text';
        $emailToView = 'AppBundle:Email:' . $action . '.' . $type . '.twig';

        return $this->render($emailToView, [
            'homepageUrl' => 'https://complete-deputy-report.service.gov.uk/',
            'domain' => 'https://complete-deputy-report.service.gov.uk/',
            'deputyFirstName' => 'Peter White',
            'fullDeputyName' => 'Peter White',
            'fullClientName'  => 'John Smith',
            'caseNumber'      => '123456789',
            'link' => 'https://complete-deputy-report.service.gov.uk/',
            'submittedReport' => new Report(),
            'newReport' => new Report(),
            'response' => [
                'satisfactionLevel' => 'Satisfied',
            ],
            'recipientRole' => MailFactory::getRecipientRole($this->getUser())
        ]);
    }

    /**
     * @Route("/behat/{secret}/logs/{action}")
     * @Template()
     */
    public function behatLogsResetAction(Request $request, $action)
    {
        $this->securityChecks($request);

        $logPath = $this->getParameter('log_path');

        switch ($action) {
            case 'reset':
                file_put_contents($logPath, "LOG RESET FROM BEHAT\n");
                return new Response('reset OK');

            case 'view':
                $lines = array_filter(array_slice(file($logPath), -500), function ($row) {
                    return strpos($row, 'translation.WARNING') === false;
                });
                $ret = implode("\n", $lines);
                return new Response($ret);
        }
    }
}
