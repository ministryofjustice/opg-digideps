<?php

namespace App\Controller\Ndr;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Exception\ReportNotSubmittableException;
use App\Exception\ReportNotSubmittedException;
use App\Exception\ReportSubmittedException;
use App\Form as FormDir;
use App\Model as ModelDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\NdrApi;
use App\Service\Client\Internal\SatisfactionApi;
use App\Service\Client\Internal\UserApi;
use App\Service\File\S3FileUploader;
use App\Service\HtmlToPdfGenerator;
use App\Service\NdrStatusService;
use App\Service\Redirector;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NdrController extends AbstractController
{
    private static $ndrGroupsForValidation = [
        'client',
        'client-ndr',
        'client-benefits-check',
        'client-case-number',
        'client-reports',
        'damages',
        'ndr',
        'ndr-action-give-gifts',
        'ndr-action-more-info',
        'ndr-action-property',
        'ndr-account',
        'ndr-asset',
        'ndr-debt',
        'ndr-debt-management',
        'ndr-expenses',
        'one-off',
        'pension',
        'report',
        'state-benefits',
        'user',
        'user-clients',
        'visits-care',
    ];

    public function __construct(
        private UserApi $userApi,
        private ClientApi $clientApi,
        private SatisfactionApi $satisfactionApi,
        private NdrApi $ndrApi,
        private HtmlToPdfGenerator $htmlToPdf,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @Route("/ndr", name="ndr_index_deprecated")
     *
     * @Template("@App/Ndr/Ndr/index.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function indexAction()
    {
        // Moved to ReportController::clientHomepageAction()
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/ndr/{ndrId}/overview", name="ndr_overview")
     *
     * @Template("@App/Ndr/Ndr/overview.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function overviewAction(Redirector $redirector, int $ndrId)
    {
        // redirect if user has missing details or is on wrong page
        $user = $this->userApi->getUserWithData();
        $route = $redirector->getCorrectRouteIfDifferent($user, 'ndr_overview');

        if (is_string($route)) {
            return $this->redirectToRoute($route);
        }

        $ndr = $this->ndrApi->getNdr($ndrId, array_merge(self::$ndrGroupsForValidation, ['ndr-client', 'client-id']));

        $clientId = $ndr->getClient()->getId();
        $client = $this->clientApi->getById($clientId);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

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
     *
     * @Template("@App/Ndr/Ndr/review.html.twig")
     */
    public function reviewAction(int $ndrId)
    {
        $ndr = $this->ndrApi->getNdr($ndrId, array_merge(self::$ndrGroupsForValidation, ['ndr-client', 'client-id']));

        $clientId = $ndr->getClient()->getId();
        $client = $this->clientApi->getById($clientId);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        // check status
        $ndrStatusService = new NdrStatusService($ndr);

        /** @var User $user */
        $user = $this->getUser();

        $backLink = $this->generateUrl('lay_home', ['clientId' => $clientId]);

        return [
            'ndr' => $ndr,
            'deputy' => $user,
            'ndrStatus' => $ndrStatusService,
            'backLink' => $backLink,
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/deputyndr.pdf", name="ndr_pdf")
     */
    public function pdfViewAction(int $ndrId)
    {
        $ndr = $this->ndrApi->getNdr($ndrId, array_merge(self::$ndrGroupsForValidation, ['ndr-client', 'client-id']));

        $clientId = $ndr->getClient()->getId();
        $client = $this->clientApi->getById($clientId);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        $pdfBinary = $this->getPdfBinaryContent($ndr);

        $response = new Response($pdfBinary);
        $response->headers->set('Content-Type', 'application/pdf');

        $attachmentName = sprintf(
            'DigiNdr-%s_%s.pdf',
            $ndr->getSubmitDate() instanceof \DateTime ? $ndr->getSubmitDate()->format('Y-m-d') : 'n-a-',
            $ndr->getClient()->getCaseNumber()
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="'.$attachmentName.'"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }

    private function getPdfBinaryContent($ndr)
    {
        /** @var string */
        $html = $this->render('@App/Ndr/Formatted/formatted_standalone.html.twig', [
            'ndr' => $ndr, 'adLoggedAsDeputy' => $this->isGranted(User::ROLE_AD),
        ])->getContent();

        return $this->htmlToPdf->getPdfFromHtml($html);
    }

    /**
     * @Route("/ndr/{ndrId}/declaration", name="ndr_declaration")
     *
     * @Template("@App/Ndr/Ndr/declaration.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws \Exception
     */
    public function declarationAction(Request $request, int $ndrId, S3FileUploader $fileUploader)
    {
        $ndr = $this->ndrApi->getNdr($ndrId, array_merge(self::$ndrGroupsForValidation, ['ndr-client', 'client-id']));

        $clientId = $ndr->getClient()->getId();
        $client = $this->clientApi->getById($clientId);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        // check status
        $ndrStatus = new NdrStatusService($ndr);
        if (!$ndrStatus->isReadyToSubmit()) {
            throw new ReportNotSubmittableException();
        }
        if ($ndr->getSubmitted()) {
            throw new ReportSubmittedException();
        }

        $form = $this->createForm(FormDir\Ndr\ReportDeclarationType::class, $ndr);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $validForSubmission = $ndr->validForSubmission();
            if (count($validForSubmission['msg']) > 0) {
                foreach ($validForSubmission['msg'] as $message) {
                    $this->logger->warning(sprintf('Client id %s has submission issue: %s', $clientId, $message));
                }

                if (!$validForSubmission['valid']) {
                    throw new \RuntimeException(sprintf('Submission validation failed for client id '.$clientId));
                }
            }

            $ndr->setSubmitted(true)->setSubmitDate(new \DateTime());

            // store PDF as a document
            $pdfBinaryContent = $this->getPdfBinaryContent($ndr);

            $document = $fileUploader->uploadFileAndPersistDocument(
                $ndr,
                $pdfBinaryContent,
                $ndr->createAttachmentName('NdrRep-%s_%s.pdf'),
                true
            );

            $this->ndrApi->submit($ndr, $document);

            return $this->redirect($this->generateUrl('ndr_submit_confirmation', ['ndrId' => $ndr->getId()]));
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
     *
     * @Template("@App/Ndr/Ndr/submitConfirmation.html.twig")
     */
    public function submitConfirmationAction(Request $request, int $ndrId)
    {
        $ndr = $this->ndrApi->getNdr($ndrId, array_merge(self::$ndrGroupsForValidation, ['ndr-client', 'client-id']));

        $clientId = $ndr->getClient()->getId();
        $client = $this->clientApi->getById($clientId);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        if ($ndr->getId() != $ndrId) {
            throw $this->createAccessDeniedException('Not authorised to access this Report');
        }

        if (!$ndr->getSubmitted()) {
            throw new ReportNotSubmittedException();
        }

        $ndrStatus = new NdrStatusService($ndr);

        $form = $this->createForm(FormDir\FeedbackReportType::class, new ModelDir\FeedbackReport());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $satisfactionId = $this->satisfactionApi->createPostSubmissionFeedback($form->getData(), $ndr->getType(), $this->getUser(), null, $ndr->getId());

            return $this->redirect($this->generateUrl('ndr_post_submission_user_research', ['ndrId' => $ndrId, 'satisfactionId' => $satisfactionId]));
        }

        return [
            'ndr' => $ndr,
            'ndrStatus' => $ndrStatus,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/ndr/{ndrId}/submit_feedback", name="ndr_submit_feedback")
     *
     * @Template("@App/Report/Report/submitFeedback.html.twig")
     */
    public function submitFeedbackAction(int $ndrId)
    {
        $ndr = $this->ndrApi->getNdr($ndrId, array_merge(self::$ndrGroupsForValidation, ['ndr-client', 'client-id']));

        $clientId = $ndr->getClient()->getId();
        $client = $this->clientApi->getById($clientId);

        if (is_null($client)) {
            throw $this->createNotFoundException();
        }

        if ($ndr->getId() != $ndrId) {
            throw $this->createAccessDeniedException('Not authorised to access this Report');
        }

        if (!$ndr->getSubmitted()) {
            throw new ReportNotSubmittedException();
        }

        return [
            'ndr' => $ndr,
            'client' => $client,
        ];
    }
}
