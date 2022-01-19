<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\Document;
use App\Entity\User;
use App\Exception\MimeTypeAndFileExtensionDoNotMatchException;
use App\Form as FormDir;
use App\Security\DocumentVoter;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\DocumentService;
use App\Service\File\S3FileUploader;
use App\Service\File\Verifier\MultiFileFormUploadVerifier;
use App\Service\StepRedirector;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class DocumentController extends AbstractController
{
    private static $jmsGroups = [
        'report-documents',
        'document-report-submission',
        'documents',
        'documents-state',
    ];

    private S3FileUploader $fileUploader;
    private RestClient $restClient;
    private ReportApi $reportApi;
    private ClientApi $clientApi;
    private StepRedirector $stepRedirector;
    private TranslatorInterface $translator;
    private DocumentService $documentService;
    private LoggerInterface $logger;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi,
        S3FileUploader $fileUploader,
        ClientApi $clientApi,
        StepRedirector $stepRedirector,
        TranslatorInterface $translator,
        DocumentService $documentService,
        LoggerInterface $logger
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
        $this->fileUploader = $fileUploader;
        $this->clientApi = $clientApi;
        $this->stepRedirector = $stepRedirector;
        $this->translator = $translator;
        $this->documentService = $documentService;
        $this->logger = $logger;
    }

    /**
     * @Route("/report/{reportId}/documents", name="documents")
     * @Template("@App/Report/Document/start.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED !== $report->getStatus()->getDocumentsState()['state']) {
            $referer = $request->headers->get('referer');

            if (is_string($referer) && false !== strpos($referer, '/step/1')) {
                return $this->redirectToRoute('report_overview', ['reportId' => $reportId]);
            } else {
                return $this->redirectToRoute('report_documents_summary', ['reportId' => $reportId, 'step' => 1]);
            }
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/documents/step", name="documents_stepzero")
     * @Route("/report/{reportId}/documents/step/1", name="documents_step")
     * @Template("@App/Report/Document/step1.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function step1Action(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $step = 1;
        $totalSteps = 3;

        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('documents', 'report_documents', 'report_documents_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(FormDir\Report\DocumentType::class, $report);
        $form->handleRequest($request);

        /** @var SubmitButton $submitBtn */
        $submitBtn = $form->get('save');

        if ($submitBtn->isClicked() && $form->isSubmitted() && $form->isValid()) {
            /* @var $data EntityDir\Report\Report */
            $data = $form->getData();

            if ('no' === $data->getWishToProvideDocumentation()) {
                if (count($data->getDeputyDocuments()) > 0) {
                    $translatedMessage = $this->translator->trans('summaryPage.setNoAttemptWithDocuments', [], 'report-documents');

                    $this->addFlash('error', $translatedMessage);
                } else {
                    $this->restClient->put('report/'.$reportId, $data, ['report', 'wish-to-provide-documentation']);
                }
            }

            $redirectUrl = 'yes' == $data->getWishToProvideDocumentation()
                ? $this->generateUrl('report_documents', ['reportId' => $report->getId()])
                : $this->generateUrl('report_documents_summary', ['reportId' => $report->getId()]);

            return $this->redirect($redirectUrl);
        }

        return [
            'report' => $report,
            'step' => $step,
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/documents/step/2", name="report_documents", defaults={"what"="new"})
     * @Template("@App/Report/Document/step2.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     *
     * @throws Exception
     */
    public function step2Action(
        Request $request,
        MultiFileFormUploadVerifier $multiFileVerifier,
                                    $reportId,
        LoggerInterface $logger
    ) {
        $report = $this->reportApi->refreshReportStatusCache($reportId, ['documents'], self::$jmsGroups);
        list($nextLink, $backLink) = $this->buildNavigationLinks($report);

        $formAction = $this->generateUrl('report_documents', ['reportId' => $reportId]);
        $form = $this->createForm(FormDir\Report\UploadType::class, null, ['action' => $formAction]);

        if ('tooBig' == $request->get('error')) {
            $message = $this->translator->trans('document.file.errors.maxSizeMessage', [], 'validators');
            $form->get('files')->addError(new FormError($message));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[]|null $uploadedFiles */
            $uploadedFiles = $request->files->get('report_document_upload')['files'];

            if (is_array($uploadedFiles)) {
                $verified = $multiFileVerifier->verify($uploadedFiles, $form, $report);

                if ($verified) {
                    try {
                        $this->fileUploader->uploadSupportingFilesAndPersistDocuments($uploadedFiles, $report);

                        return $this->redirectToRoute('report_documents', ['reportId' => $reportId, 'successUploaded' => 'true']);
                    } catch (MimeTypeAndFileExtensionDoNotMatchException $e) {
                        $errorMessage = sprintf('Cannot upload file: %s.', $e->getMessage());
                        $logger->warning($errorMessage);

                        $form->get('files')->addError(new FormError($errorMessage));
                    } catch (Throwable $e) {
                        $logger->warning('Error uploading file: '.$e->getMessage());

                        $form->get('files')->addError(new FormError('Cannot upload file, please try again later'));
                    }
                }
            }
        }

        return [
            'report' => $report,
            'step' => $request->get('step'), // if step is set, this is used to show the save and continue button
            'backLink' => $backLink,
            'nextLink' => $nextLink,
            'successUploaded' => $request->get('successUploaded'),
            'form' => $form->createView(),
        ];
    }

    /**
     * @throws Exception
     */
    private function buildNavigationLinks(EntityDir\Report\Report $report): array
    {
        if (!$report->isSubmitted()) {
            $nextLink = $this->generateUrl('report_documents_summary', ['reportId' => $report->getId(), 'step' => 3, 'from' => 'report_documents']);
            $backLink = $this->generateUrl('documents_step', ['reportId' => $report->getId(), 'step' => 1]);
        } else {
            $nextLink = $this->generateUrl('report_documents_submit_more', ['reportId' => $report->getId(), 'from' => 'report_documents']);

            /** @var User $user */
            $user = $this->getUser();

            if ($user->isDeputyOrg()) {
                $backLink = $this->clientApi->generateClientProfileLink($report->getClient());
            } else {
                $backLink = $this->generateUrl('homepage');
            }
        }

        return [$nextLink, $backLink];
    }

    /**
     * @Route("/report/{reportId}/documents/summary", name="report_documents_summary")
     * @Template("@App/Report/Document/summary.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getDocumentsState()['state']) {
            return $this->redirectToRoute('documents', ['reportId' => $report->getId()]);
        }

        $fromPage = $request->get('from');

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'backLink' => $this->generateUrl('report_documents', ['reportId' => $report->getId()]),
            'status' => $report->getStatus(),
        ];
    }

    /**
     * Confirm delete document form.
     *
     * @Route("/documents/{documentId}/delete", name="delete_document")
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @param $documentId
     *
     * @return array|RedirectResponse|Response
     */
    public function deleteConfirmAction(Request $request, $documentId)
    {
        $document = $this->getDocument($documentId);

        if ($document->getReportSubmission() instanceof EntityDir\Report\ReportSubmission) {
            return $this->renderError('Document already submitted and cannot be removed.', Response::HTTP_FORBIDDEN);
        }

        $this->denyAccessUnlessGranted(DocumentVoter::DELETE_DOCUMENT, $document, 'Access denied');

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->deleteDocument($request, $documentId);
        }

        $report = $document->getReport();
        $fromPage = $request->get('from');

        $backLink = 'summaryPage' == $fromPage
            ? $this->generateUrl('report_documents_summary', ['reportId' => $report->getId()])
            : $this->generateUrl('report_documents', ['reportId' => $report->getId()]);

        return [
            'translationDomain' => 'report-documents',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.fileName', 'value' => $document->getFileName()],
                ['label' => 'deletePage.summary.createdOn', 'value' => $document->getCreatedOn(), 'format' => 'date'],
            ],
            'backLink' => $backLink,
        ];
    }

    /**
     * Retrieves the document object with required associated entities to populate the table and back links.
     *
     * @return Document
     */
    private function getDocument(string $documentId)
    {
        return $this->restClient->get(
            'document/'.$documentId,
            'Report\Document',
            ['documents', 'status', 'document-storage-reference', 'document-report-submission', 'document-report', 'report', 'report-client', 'client', 'client-users', 'user-id', 'client-organisations']
        );
    }

    /**
     * Removes a document, adds a flash message and redirects to page.
     *
     * @param $documentId
     *
     * @return RedirectResponse
     */
    public function deleteDocument(Request $request, $documentId)
    {
        $document = $this->getDocument($documentId);

        $report = $document->getReport();
        $this->denyAccessUnlessGranted(DocumentVoter::DELETE_DOCUMENT, $document, 'Access denied');

        try {
            $result = $this->documentService->removeDocumentFromS3($document); // rethrows any exception

            if ($result) {
                $this->addFlash('notice', 'Document has been removed');
            }
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());

            $this->addFlash(
                'error',
                'Document could not be removed. Details: '.$e->getMessage()
            );
        }

        if ($report->isSubmitted()) {
            // if report is submitted, then this remove path has come from adding additional documents so return the user
            // to the step 2 page.
            $returnUrl = $this->generateUrl('report_documents', ['reportId' => $document->getReportId()]);
        } else {
            $reportDocumentStatus = $report->getStatus()->getDocumentsState();
            if (array_key_exists('nOfRecords', $reportDocumentStatus) && is_numeric($reportDocumentStatus['nOfRecords']) && $reportDocumentStatus['nOfRecords'] > 1) {
                $returnUrl = 'summaryPage' == $request->get('from')
                    ? $this->generateUrl('report_documents_summary', ['reportId' => $document->getReportId()])
                    : $this->generateUrl('report_documents', ['reportId' => $document->getReportId()]);
            } else {
                $returnUrl = $this->generateUrl('documents_step', ['reportId' => $document->getReportId()]);
            }
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Confirm additional documents form.
     *
     * @Route("/report/{reportId}/documents/submit-more", name="report_documents_submit_more")
     * @Template("@App/Report/Document/submitMoreDocumentsConfirm.html.twig")
     *
     * @param $reportId
     *
     * @return array
     */
    public function submitMoreConfirmAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReport($reportId, self::$jmsGroups);

        $fromPage = $request->get('from');

        $backLink = $this->generateUrl('report_documents', ['reportId' => $reportId]);
        $nextLink = $this->generateUrl('report_documents_submit_more_confirmed', ['reportId' => $reportId]);

        return [
            'report' => $report,
            'backLink' => $backLink,
            'nextLink' => $nextLink,
            'fromPage' => $fromPage,
        ];
    }

    /**
     * Confirmed send additional documents.
     *
     * @Route("/report/{reportId}/documents/confirm-submit-more", name="report_documents_submit_more_confirmed")
     * @Template("@App/Report/Document/submitMoreDocumentsConfirmed.html.twig")
     *
     * @param $reportId
     *
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function submitMoreConfirmedAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReport($reportId, self::$jmsGroups);

        // submit the report to generate the submission entry only
        $this->restClient->put('report/'.$report->getId().'/submit-documents', $report, ['submit']);

        $this->addFlash('notice', 'The documents attached for your '.$report->getPeriod().' report have been sent to OPG');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isDeputyOrg()) {
            return $this->redirect($this->clientApi->generateClientProfileLink($report->getClient()));
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'documents';
    }
}
