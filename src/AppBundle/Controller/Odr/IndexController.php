<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity\Report\Report;
use AppBundle\Form\Odr\ReportDeclarationType;
use AppBundle\Service\OdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    private static $odrGroupsForValidation = [
        'user',
        'client',
        'odr',
        'report',
        'visits-care',
        'odr-account',
        'odr-debt',
        'odr-asset',
        'odr-income-benefits',
        'odr-income-state-benefits',
        'odr-income-pension',
        'odr-income-damages',
        'odr-income-one-off',
        'odr-expenses',
        'odr-action-give-gifts',
        'odr-action-property',
        'odr-action-more-info',
    ];

    /**
     * //TODO move view into Odr directory when branches are integrated.
     *
     * @Route("/odr", name="odr_index")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->getUserWithData(self::$odrGroupsForValidation);

        // in case the user jumps to this page directly via URL
        if (!$user->isOdrEnabled()) {
            return $this->redirectToRoute('reports', ['cot' => Report::PROPERTY_AND_AFFAIRS]);
        }

        $client = $user->getClients()[0];
        $odr = $client->getOdr();

        $reports = $client ? $client->getReports() : [];
        arsort($reports);

        $reportActive = null;
        $reportsSubmitted = [];
        foreach ($reports as $currentReport) {
            if ($currentReport->getSubmitted()) {
                $reportsSubmitted[] = $currentReport;
            } else {
                $reportActive = $currentReport;
            }
        }

        $odrStatus = new OdrStatusService($odr);

        return [
            'client' => $client,
            'odr' => $odr,
            'reportsSubmitted' => $reportsSubmitted,
            'reportActive' => $reportActive,
            'odrStatus' => $odrStatus,
        ];
    }

    /**
     * @Route("/odr/overview", name="odr_overview")
     * @Template()
     */
    public function overviewAction()
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();

        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }
        $odrStatus = new OdrStatusService($odr);

        return [
            'client' => $client,
            'odr' => $odr,
            'odrStatus' => $odrStatus,
        ];
    }

    /**
     * Used for active and archived ODRs.
     *
     * @Route("/odr/review", name="odr_review")
     * @Template()
     */
    public function reviewAction()
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        // check status
        $odrStatusService = new OdrStatusService($odr);

        return [
            'odr' => $odr,
            'deputy' => $this->getUser(),
            'odrStatus' => $odrStatusService,
        ];
    }

    /**
     * @Route("/odr/deputyodr.pdf", name="odr_pdf")
     */
    public function pdfViewAction()
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        $pdfBinary = $this->getPdfBinaryContent($odr);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf('DigiOdr-%s_%s.pdf',
            $odr->getSubmitDate() ? $odr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $odr->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="'.$attachmentName.'"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($odr)
    {
        $html = $this->render('AppBundle:Odr/Formatted:formatted_body.html.twig', array(
            'odr' => $odr,
        ))->getContent();

        return $this->get('wkhtmltopdf')->getPdfFromHtml($html);
    }

    /**
     * @Route("/odr/declaration", name="odr_declaration")
     * @Template()
     */
    public function declarationAction(Request $request)
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        // check status
        $odrStatus = new OdrStatusService($odr);
        if (!$odrStatus->isReadyToSubmit()) {
            throw new \RuntimeException('Report not ready for submission');
        }
        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        $user = $this->getUserWithData(['user', 'role', 'client']);
        $clients = $user->getClients();
        $client = $clients[0];

        $form = $this->createForm(new ReportDeclarationType(), $odr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date
            $odr->setSubmitted(true)->setSubmitDate(new \DateTime());
            $this->getRestClient()->put('odr/'.$odr->getId().'/submit', $odr, ['submit']);

            $pdfBinaryContent = $this->getPdfBinaryContent($odr);
            $reportEmail = $this->getMailFactory()->createOdrEmail($this->getUser(), $odr, $pdfBinaryContent);
            $this->getMailSender()->send($reportEmail, ['html'], 'secure-smtp');

            //send confirmation email
            $reportConfirmEmail = $this->getMailFactory()->createOdrSubmissionConfirmationEmail($this->getUser(), $odr);
            $this->getMailSender()->send($reportConfirmEmail, ['text', 'html']);

            return $this->redirect($this->generateUrl('odr_submit_confirmation'));
        }

        return [
            'odr' => $odr,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }

    /**
     * Page displaying the report has been submitted.
     *
     * @Route("/odr/submitted", name="odr_submit_confirmation")
     * @Template()
     */
    public function submitConfirmationAction(Request $request)
    {
        $client = $this->getFirstClient(self::$odrGroupsForValidation);
        $odr = $client->getOdr();
        $odr->setClient($client);

        if (!$odr->getSubmitted()) {
            throw new \RuntimeException('Report not submitted');
        }

        $odrStatus = new OdrStatusService($odr);

        return [
            'odr' => $odr,
            'odrStatus' => $odrStatus,
            'homePageHeaderLink' => $this->generateUrl('client_show'),
        ];
    }
}
