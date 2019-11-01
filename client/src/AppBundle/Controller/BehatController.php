<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BehatController extends AbstractController
{
    private function securityChecks(Request $request)
    {
        if (!$this->container->getParameter('mock_emails')) {
            throw $this->createNotFoundException();
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
     * @Route("/behat/{secret}/email-get-last", methods={"GET"})
     */
    public function getLastEmailAction(Request $request)
    {
        $this->securityChecks($request);

        echo $this->get('mail_sender')->getMockedEmailsRaw();
        die; //TODO check if works with response
    }

    /**
     * @Route("/behat/{secret}/email-reset", methods={"GET"})
     */
    public function emailResetAction(Request $request)
    {
        $this->securityChecks($request);

        $this->get('mail_sender')->resetMockedEmails();
        return new Response('Email reset successfully');
    }

    /**
     * @Route("/behat/emails", methods={"GET"})
     * @Template("AppBundle:Behat:emails.html.twig")
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
}
