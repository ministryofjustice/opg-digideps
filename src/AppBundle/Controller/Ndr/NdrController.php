<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Form as FormDir;
use AppBundle\Service\NdrStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NdrController extends AbstractController
{
    private static $ndrGroupsForValidation = [
        'user',
        'user-clients',
        'client',
        'client-ndr',
        'client-reports',
        'client-case-number',
        'ndr',
        'report',
        'visits-care',
        'ndr-account',
        'ndr-debt',
        'ndr-debt-management',
        'ndr-asset',
        'state-benefits',
        'pension',
        'damages',
        'one-off',
        'ndr-expenses',
        'ndr-action-give-gifts',
        'ndr-action-property',
        'ndr-action-more-info',
    ];

    /**
     * //TODO move view into Ndr directory when branches are integrated.
     *
     * @Route("/ndr", name="ndr_index")
     * @Template()
     */
    public function indexAction()
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData(array_merge(self::$ndrGroupsForValidation, ['status']));
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'ndr_index')) {
            return $this->redirectToRoute($route);
        }

        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;
        $coDeputies = !empty($client) ? $client->getCoDeputies() : [];
        $ndr = $client->getNdr();

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

        $ndrStatus = new NdrStatusService($ndr);

        return [
            'client' => $client,
            'coDeputies' => $coDeputies,
            'ndr' => $ndr,
            'reportsSubmitted' => $reportsSubmitted,
            'reportActive' => $reportActive,
            'ndrStatus' => $ndrStatus
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/overview", name="ndr_overview")
     * @Template()
     */
    public function overviewAction($ndrId)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData();
        if ($route = $this->get('redirector_service')->getCorrectRouteIfDifferent($user, 'ndr_overview')) {
            return $this->redirectToRoute($route);
        }

        $client = $this->getFirstClient(self::$ndrGroupsForValidation);
        $ndr = $client->getNdr();
        if ($ndr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }
        $ndrStatus = new NdrStatusService($ndr);

        return [
            'client' => $client,
            'ndr' => $ndr,
            'ndrStatus' => $ndrStatus,
        ];
    }

    /**
     * Used for active and archived NDRs.
     *
     * @Route("/ndr/{ndrId}/review", name="ndr_review")
     * @Template()
     */
    public function reviewAction($ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);
        $ndr = $client->getNdr();
        $ndr->setClient($client);

        // check status
        $ndrStatusService = new NdrStatusService($ndr);

        return [
            'ndr' => $ndr,
            'deputy' => $this->getUser(),
            'ndrStatus' => $ndrStatusService,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputyndr.pdf", name="ndr_pdf")
     */
    public function pdfViewAction($ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);
        $ndr = $client->getNdr();
        $ndr->setClient($client);

        $pdfBinary = $this->getPdfBinaryContent($ndr);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf('DigiNdr-%s_%s.pdf',
            $ndr->getSubmitDate() ? $ndr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $ndr->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($ndr)
    {
        $html = $this->render('AppBundle:Ndr/Formatted:formatted_body.html.twig', [
            'ndr' => $ndr, 'adLoggedAsDeputy' => $this->isGranted(User::ROLE_AD)
        ])->getContent();

        return $this->get('wkhtmltopdf')->getPdfFromHtml($html);
    }

    /**
     * @Route("/ndr/{ndrId}/declaration", name="ndr_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);
        $ndr = $client->getNdr();
        $ndr->setClient($client);

        // check status
        $ndrStatus = new NdrStatusService($ndr);
        if (!$ndrStatus->isReadyToSubmit()) {
            throw new \RuntimeException('Report not ready for submission');
        }
        if ($ndr->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }

        $user = $this->getUserWithData(['user-clients', 'client']);
        $clients = $user->getClients();
        $client = $clients[0];

        $form = $this->createForm(FormDir\Ndr\ReportDeclarationType::class, $ndr);
        $form->handleRequest($request);
        if ($form->isValid()) {
            // set report submitted with date

            $ndr->setSubmitted(true)->setSubmitDate(new \DateTime());

            // store PDF as a document
            $pdfBinaryContent = $this->getPdfBinaryContent($ndr);
            $fileUploader = $this->get('file_uploader');

            $document = $fileUploader->uploadFile(
                $ndr,
                $pdfBinaryContent,
                $ndr->createAttachmentName('NdrRep-%s_%s.pdf'),
                true
            );

            $this->getRestClient()->put('ndr/' . $ndr->getId() . '/submit?documentId=' . $document->getId(), $ndr, ['submit']);

            $pdfBinaryContent = $this->getPdfBinaryContent($ndr);
            $reportEmail = $this->getMailFactory()->createNdrEmail($this->getUser(), $ndr, $pdfBinaryContent);
            $this->getMailSender()->send($reportEmail, ['html'], 'secure-smtp');

            //send confirmation email
            $reportConfirmEmail = $this->getMailFactory()->createNdrSubmissionConfirmationEmail($this->getUser(), $ndr);
            $this->getMailSender()->send($reportConfirmEmail, ['text', 'html']);

            return $this->redirect($this->generateUrl('ndr_submit_confirmation', ['ndrId'=>$ndr->getId()]));
        }

        return [
            'ndr' => $ndr,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }

    /**
     * Page displaying the report has been submitted.
     *
     * @Route("/ndr/{ndrId}/submitted", name="ndr_submit_confirmation")
     * @Template()
     */
    public function submitConfirmationAction(Request $request, $ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);
        $ndr = $client->getNdr();
        if ($ndr->getId() != $ndrId) {
            throw new \RuntimeException('Not authorised to access this Report');
        }
        $ndr->setClient($client);

        if (!$ndr->getSubmitted()) {
            throw new \RuntimeException('Report not submitted');
        }

        $ndrStatus = new NdrStatusService($ndr);

        return [
            'ndr' => $ndr,
            'ndrStatus' => $ndrStatus,
        ];
    }
}
