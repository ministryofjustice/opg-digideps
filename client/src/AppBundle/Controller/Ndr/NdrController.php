<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\User;
use AppBundle\Exception\ReportNotSubmittableException;
use AppBundle\Exception\ReportNotSubmittedException;
use AppBundle\Exception\ReportSubmittedException;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\NdrStatusService;
use AppBundle\Service\Redirector;
use AppBundle\Service\WkHtmlToPdfGenerator;
use Symfony\Component\Routing\Annotation\Route;
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

    /** @var WkHtmlToPdfGenerator */
    private $htmlToPdf;

    public function __construct(WkHtmlToPdfGenerator $wkHtmlToPdfGenerator)
    {
        $this->htmlToPdf = $wkHtmlToPdfGenerator;
    }

    /**
     * //TODO move view into Ndr directory when branches are integrated.
     *
     * @Route("/ndr", name="ndr_index")
     * @Template("AppBundle:Ndr/Ndr:index.html.twig")
     */
    public function indexAction(Redirector $redirector)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData(array_merge(self::$ndrGroupsForValidation, ['status']));

        $route = $redirector->getCorrectRouteIfDifferent($user, 'ndr_index');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $clients = $user->getClients();
        $client = !empty($clients) ? $clients[0] : null;
        $coDeputies = !empty($client) ? $this->getCoDeputiesForClient($user) : [];

        return [
            'client' => $client,
            'coDeputies' => $coDeputies,
            'ndr' => $client->getNdr(),
            'reportsSubmitted' => $client->getSubmittedReports(),
            'reportActive' => $client->getActiveReport(),
            'ndrStatus' => new NdrStatusService($client->getNdr())
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/overview", name="ndr_overview")
     * @Template("AppBundle:Ndr/Ndr:overview.html.twig")
     */
    public function overviewAction($ndrId, Redirector $redirector)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->getUserWithData();
        $route = $redirector->getCorrectRouteIfDifferent($user, 'ndr_overview');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $client = $this->getFirstClient(self::$ndrGroupsForValidation);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        $ndr = $client->getNdr();
        if ($ndr->getSubmitted()) {
            throw new ReportSubmittedException();
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
     * @Template("AppBundle:Ndr/Ndr:review.html.twig")
     */
    public function reviewAction($ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

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

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        $ndr = $client->getNdr();
        $ndr->setClient($client);

        $pdfBinary = $this->getPdfBinaryContent($ndr);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf('DigiNdr-%s_%s.pdf',
            $ndr->getSubmitDate() instanceof \DateTime ? $ndr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $ndr->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($ndr)
    {
        /** @var string */
        $html = $this->render('AppBundle:Ndr/Formatted:formatted_standalone.html.twig', [
            'ndr' => $ndr, 'adLoggedAsDeputy' => $this->isGranted(User::ROLE_AD)
        ])->getContent();

        return $this->htmlToPdf->getPdfFromHtml($html);
    }

    /**
     * @Route("/ndr/{ndrId}/declaration", name="ndr_declaration")
     * @Template("AppBundle:Ndr/Ndr:declaration.html.twig")
     */
    public function declarationAction(Request $request, $ndrId, FileUploader $fileUploader)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        $ndr = $client->getNdr();
        $ndr->setClient($client);

        // check status
        $ndrStatus = new NdrStatusService($ndr);
        if (!$ndrStatus->isReadyToSubmit()) {
            throw new ReportNotSubmittableException();
        }
        if ($ndr->getSubmitted()) {
            throw new ReportSubmittedException();
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

            $document = $fileUploader->uploadFile(
                $ndr,
                $pdfBinaryContent,
                $ndr->createAttachmentName('NdrRep-%s_%s.pdf'),
                true
            );

            $this->getRestClient()->put('ndr/' . $ndr->getId() . '/submit?documentId=' . $document->getId(), $ndr, ['submit']);

            /** @var User */
            $user = $this->getUserWithData(['user-clients', 'report', 'client-reports']);
            $client = $user->getClients()[0];

            $reportConfirmEmail = $this->getMailFactory()->createNdrSubmissionConfirmationEmail($user, $ndr, $client->getActiveReport());
            $this->getMailSender()->send($reportConfirmEmail);

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
     * @Template("AppBundle:Ndr/Ndr:submitConfirmation.html.twig")
     */
    public function submitConfirmationAction(Request $request, $ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        $ndr = $client->getNdr();
        if ($ndr->getId() != $ndrId) {
            throw $this->createAccessDeniedException('Not authorised to access this Report');
        }
        $ndr->setClient($client);

        if (!$ndr->getSubmitted()) {
            throw new ReportNotSubmittedException();
        }

        $ndrStatus = new NdrStatusService($ndr);

        $form = $this->createForm(FormDir\FeedbackReportType::class, new ModelDir\FeedbackReport());

        $form->handleRequest($request);

        if ($form->isValid()) {
            // Store in database
            $this->getRestClient()->post('satisfaction', [
                'score' => $form->get('satisfactionLevel')->getData(),
                'comments' => $form->get('comments')->getData(),
                'reportType' => $ndr->getType(),
            ]);

            /** @var User */
            $user = $this->getUser();

            // Send notification email
            $feedbackEmail = $this->getMailFactory()->createPostSubmissionFeedbackEmail($form->getData(), $user);
            $this->getMailSender()->send($feedbackEmail, ['html']);

            return $this->redirect($this->generateUrl('ndr_submit_feedback', ['ndrId' => $ndrId]));
        }

        return [
            'ndr' => $ndr,
            'ndrStatus' => $ndrStatus,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/submit_feedback", name="ndr_submit_feedback")
     * @Template("AppBundle:Report:Report/submitFeedback.html.twig")
     */
    public function submitFeedbackAction($ndrId)
    {
        $client = $this->getFirstClient(self::$ndrGroupsForValidation);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        $ndr = $client->getNdr();
        if ($ndr->getId() != $ndrId) {
            throw $this->createAccessDeniedException('Not authorised to access this Report');
        }
        $ndr->setClient($client);

        if (!$ndr->getSubmitted()) {
            throw new ReportNotSubmittedException();
        }

        $ndrStatus = new NdrStatusService($ndr);

        return [
            'ndr' => $ndr,
        ];
    }

    /**
     * @param User $user
     * @return mixed
     */
    private function getCoDeputiesForClient(User $user)
    {
        return $this->hydrateClientWithUsers($user)->getCoDeputies();
    }

    /**
     * @param User $user
     * @return mixed
     */
    private function hydrateClientWithUsers(User $user)
    {
        $clients = $user->getClients();
        $clientId = array_shift($clients)->getId();

        return  $this->getRestClient()->get(
            'client/' . $clientId,
            'Client',
            ['client', 'client-users', 'user']
        );
    }
}
