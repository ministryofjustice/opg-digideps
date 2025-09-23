<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\Report\Document;
use App\Entity\User;
use App\Exception\MimeTypeAndFileExtensionDoNotMatchException;
use App\Form\ConfirmDeleteType;
use App\Form\Report\DocumentType;
use App\Form\Report\UploadType;
use App\Security\DocumentVoter;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\DocumentService;
use App\Service\File\S3FileUploader;
use App\Service\File\Storage\FileUploadFailedException;
use App\Service\File\Storage\S3Storage;
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

class DocumentController extends AbstractController
{
    private static $jmsGroups = [
        'report-documents',
        'document-report-submission',
        'documents',
        'documents-state',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private S3FileUploader $fileUploader,
        private ClientApi $clientApi,
        private StepRedirector $stepRedirector,
        private TranslatorInterface $translator,
        private DocumentService $documentService,
        private LoggerInterface $logger,
        private S3Storage $s3Storage,
    ) {
    }

    /**
     * @Route("/report/{reportId}/documents", name="documents")
     *
     * @Template("@App/Report/Document/start.html.twig")
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
     *
     * @Template("@App/Report/Document/step1.html.twig")
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

        $form = $this->createForm(DocumentType::class, $report);
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
     * @Route("/report/{reportId}/documents/step/2", defaults={"what"="new"}, name="report_documents")
     *
     * @Template("@App/Report/Document/step2.html.twig")
     *
     * @return array|RedirectResponse
     *
     * @throws \Exception
     */
    public function step2Action(
        Request $request,
        MultiFileFormUploadVerifier $multiFileVerifier,
        string $reportId,
        LoggerInterface $logger,
    ) {
        $report = $this->reportApi->refreshReportStatusCache($reportId, ['documents'], self::$jmsGroups);

        $formAction = $this->generateUrl('report_documents', ['reportId' => $reportId]);
        $form = $this->createForm(UploadType::class, null, [
            'action' => $formAction, 'report_submitted' => $report->isSubmitted(),
        ]);

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
                    } catch (FileUploadFailedException $e) {
                        $errorMessage = sprintf('File "%s" upload did not complete, please try again', $e->getMessage());

                        $form->get('files')->addError(new FormError($errorMessage));
                    } catch (\Throwable $e) {
                        $logger->warning('Error uploading file: '.$e->getMessage());

                        $form->get('files')->addError(new FormError('Cannot upload file, please try again later'));
                    }
                }
            }
        }

        /** @var string $successfullyUploaded */
        $successfullyUploaded = $request->get('successUploaded');

        list($nextLink, $backLink) = $this->buildNavigationLinks($report, $successfullyUploaded);

        return [
            'report' => $report,
            'step' => $request->get('step'), // if step is set, this is used to show the save and continue button
            'backLink' => $backLink,
            'nextLink' => $nextLink,
            'successUploaded' => $successfullyUploaded,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/documents/submissionRedirect", name="report_documents_submit_more_redirect")
     *
     * @Template("@App/Report/Document/submitMoreDocumentsConfirmed.html.twig")
     *
     * @throws \Exception
     */
    public function handleRedirectPostDocSubmission(Request $request): RedirectResponse
    {
        $reportId = $request->query->getInt('reportId');
        $report = $this->reportApi->getReport($reportId, self::$jmsGroups);

        // submit the report to generate the submission entry only
        $this->restClient->put('report/'.$reportId.'/submit-documents', $report, ['submit']);

        if ('true' === $request->get('successUploaded')) {
            $this->addFlash('fileUploadSuccess', 'Your uploaded files are now attached to this report.');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isDeputyOrg()) {
            return $this->redirect($this->clientApi->generateClientProfileLink($report->getClient()));
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/report/{reportId}/documents/reupload", name="report_documents_reupload")
     *
     * @Template("@App/Report/Document/reupload.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function documentReUpload(
        Request $request,
        MultiFileFormUploadVerifier $multiFileVerifier,
        string $reportId,
        LoggerInterface $logger,
    ) {
        $report = $this->reportApi->refreshReportStatusCache($reportId, ['documents'], self::$jmsGroups);

        $backLink = $this->generateUrl('report_overview', ['reportId' => $report->getId()]);

        $formAction = $this->generateUrl('report_documents_reupload', ['reportId' => $reportId]);
        $form = $this->createForm(UploadType::class, null, ['action' => $formAction, 'report_submitted' => $report->isSubmitted()]);

        if ('tooBig' == $request->get('error')) {
            $message = $this->translator->trans('document.file.errors.maxSizeMessage', [], 'validators');
            $form->get('files')->addError(new FormError($message));
        }

        $form->handleRequest($request);

        // identify docs that require re-uploading
        $documentsToBeReUploaded = $this->identifyMissingFilesInS3Bucket($report);

        // holds a boolean value based on whether other documents exist in S3 that are submittable
        // to assist with conditional rendering in the view
        $documentsAccessibleInS3 = count($report->getDocuments()) > count($documentsToBeReUploaded);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[]|null $uploadedFiles */
            $uploadedFiles = $request->files->get('report_document_upload')['files'];

            if (is_array($uploadedFiles)) {
                $verified = $multiFileVerifier->verify($uploadedFiles, $form, $report);

                if ($verified) {
                    try {
                        $this->fileUploader->uploadSupportingFilesAndPersistDocuments($uploadedFiles, $report);
                        $this->addFlash('notice', 'Document has been uploaded');

                        return $this->redirectToRoute('report_documents_reupload', ['reportId' => $reportId, 'successUploaded' => 'true']);
                    } catch (MimeTypeAndFileExtensionDoNotMatchException $e) {
                        $errorMessage = sprintf('Cannot upload file: %s.', $e->getMessage());
                        $logger->warning($errorMessage);

                        $form->get('files')->addError(new FormError($errorMessage));
                    } catch (FileUploadFailedException $e) {
                        $errorMessage = sprintf('File "%s" upload did not complete, please try again', $e->getMessage());

                        $form->get('files')->addError(new FormError($errorMessage));
                    } catch (\Throwable $e) {
                        $logger->warning('Error uploading file: '.$e->getMessage());

                        $form->get('files')->addError(new FormError('Cannot upload file, please try again later'));
                    }
                }
            }
        }

        $saveAndContinueLink = $this->generateUrl('report_overview', ['reportId' => $report->getId(), 'from' => 'report_documents_reupload']);

        return [
            'report' => $report,
            'step' => $request->get('step'), // if step is set, this is used to show the save and continue button
            'backLink' => $backLink,
            'saveAndContinueLink' => $saveAndContinueLink,
            'successUploaded' => $request->get('successUploaded'),
            'form' => $form->createView(),
            'documentsToBeReUploaded' => $documentsToBeReUploaded,
            'documentsAccessibleInS3' => $documentsAccessibleInS3,
        ];
    }

    private function identifyMissingFilesInS3Bucket(EntityDir\Report\Report $report): array
    {
        $documentIds = [];

        foreach ($report->getDeputyDocuments() as $document) {
            $documentIds[] = $document->getId();
        }

        $uploadedDocuments = [];
        foreach ($documentIds as $documentId) {
            $uploadedDocuments[] = $this->restClient->get(
                sprintf('document/%s', $documentId),
                'Report\Document',
                ['document-storage-reference', 'documents']
            );
        }

        // call Document Service and check if documents exist in the S3 bucket
        $documentsNotInS3 = [];

        // loop through references and check if they exist in S3
        if (!empty($uploadedDocuments)) {
            foreach ($uploadedDocuments as $uploadedDocument) {
                if (!$this->s3Storage->checkFileExistsInS3($uploadedDocument->getStorageReference())) {
                    $documentsNotInS3[] = $uploadedDocument->getStorageReference();
                }
            }
        }

        return $documentsNotInS3;
    }

    /**
     * @throws \Exception
     */
    private function buildNavigationLinks(EntityDir\Report\Report $report, ?string $successfulUpload = null): array
    {
        if (!$report->isSubmitted()) {
            $nextLink = $this->generateUrl('report_documents_summary', ['reportId' => $report->getId(), 'step' => 3, 'from' => 'report_documents']);
            $backLink = $this->generateUrl('documents_step', ['reportId' => $report->getId(), 'step' => 1]);
        } else {
            /** @var User $user */
            $user = $this->getUser();

            if ('true' === $successfulUpload) {
                $nextLink = $this->generateUrl('report_documents_submit_more_redirect', ['reportId' => $report->getId(), 'successUploaded' => $successfulUpload]);
            } else {
                $nextLink = $this->generateUrl('report_documents_submit_more_redirect', ['reportId' => $report->getId()]);
            }

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
     *
     * @Template("@App/Report/Document/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, int $reportId)
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
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse|Response
     */
    public function deleteConfirmAction(Request $request, string $documentId)
    {
        $document = $this->getDocument($documentId);

        if ($document->getReportSubmission() instanceof EntityDir\Report\ReportSubmission) {
            return $this->renderError('Document already submitted and cannot be removed.', Response::HTTP_FORBIDDEN);
        }

        $this->denyAccessUnlessGranted(DocumentVoter::DELETE_DOCUMENT, $document, 'Access denied');

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->deleteDocument($request, $documentId);
        }

        $report = $document->getReport();
        $fromPage = $request->get('from');

        if ('reUploadPage' == $fromPage) {
            $backLink = $this->generateUrl('report_documents_reupload', ['reportId' => $document->getReportId()]);
        } elseif ('summaryPage' == $fromPage) {
            $backLink = $this->generateUrl('report_documents_summary', ['reportId' => $report->getId()]);
        } else {
            $backLink = $this->generateUrl('report_documents', ['reportId' => $report->getId()]);
        }

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
     * @return RedirectResponse
     */
    public function deleteDocument(Request $request, string $documentId)
    {
        $document = $this->getDocument($documentId);

        $report = $document->getReport();
        $this->denyAccessUnlessGranted(DocumentVoter::DELETE_DOCUMENT, $document, 'Access denied');

        // If document needs to be re-uploaded because it's missing from S3 bucket then delete Document object
        $documentNotInS3 = $request->get('notInS3');

        if ($documentNotInS3) {
            $this->deleteMissingS3DocFromDocumentTable($documentId);
        } else {
            try {
                $result = $this->documentService->removeDocumentFromS3($document); // rethrows any exception

                if ($result && !$report->isSubmitted()) {
                    $this->addFlash('notice', 'Document has been removed');
                } elseif ($result && $report->isSubmitted()) {
                    $documentName = $document->getFileName();
                    $this->addFlash('fileRemovalSuccess', sprintf('File named %s has been removed', $documentName));
                }
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());

                $this->addFlash(
                    'error',
                    'Document could not be removed. Details: '.$e->getMessage()
                );
            }
        }

        if ($report->isSubmitted()) {
            // if report is submitted, then this remove path has come from adding additional documents so return the user to the step 2 page.
            $returnUrl = $this->generateUrl('report_documents', ['reportId' => $document->getReportId()]);
        } else {
            $reportDocumentStatus = $report->getStatus()->getDocumentsState();

            if ('reUploadPage' == $request->get('from')) {
                $returnUrl = $this->generateUrl('report_documents_reupload', ['reportId' => $document->getReportId()]);
            } elseif (array_key_exists('nOfRecords', $reportDocumentStatus) && is_numeric($reportDocumentStatus['nOfRecords']) && $reportDocumentStatus['nOfRecords'] > 1) {
                $returnUrl = 'summaryPage' == $request->get('from')
                    ? $this->generateUrl('report_documents_summary', ['reportId' => $document->getReportId()])
                    : $this->generateUrl('report_documents', ['reportId' => $document->getReportId()]);
            } else {
                $returnUrl = $this->generateUrl('documents_step', ['reportId' => $document->getReportId()]);
            }
        }

        return $this->redirect($returnUrl);
    }

    private function deleteMissingS3DocFromDocumentTable(string $documentId): void
    {
        $this->restClient->delete('/document/'.$documentId);
        $this->addFlash('notice', 'Document has been removed');
    }

    /**
     * @Route("/report/documents/deleteFilePostSubmission", name="delete_report_documents_post_submission")
     */
    public function deleteSelectedFilePostReportSubmission(Request $request): RedirectResponse
    {
        $documentId = $request->query->getInt('documentId');

        return $this->deleteDocument($request, strval($documentId));
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'documents';
    }
}
