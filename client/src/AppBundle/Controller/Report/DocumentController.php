<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Document as Document;
use AppBundle\Form as FormDir;
use AppBundle\Security\DocumentVoter;
use AppBundle\Service\DocumentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends AbstractController
{
    private static $jmsGroups = [
        'report-documents',
        'document-report-submission',
        'documents',
        'documents-state',
    ];

    /**
     * @Route("/report/{reportId}/documents", name="documents")
     * @Template("AppBundle:Report/Document:start.html.twig")
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getDocumentsState()['state'] !== EntityDir\Report\Status::STATE_NOT_STARTED) {
            $referer = $request->headers->get('referer');
            $redirectResponse = false !== strpos($referer, '/step/1')
                ? $this->redirectToRoute('report_overview', ['reportId' => $reportId])
                : $this->redirectToRoute('report_documents_summary', ['reportId' => $reportId, 'step' => 1]);
            return $redirectResponse;
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/documents/step", name="documents_stepzero")
     * @Route("/report/{reportId}/documents/step/1", name="documents_step")
     * @Template("AppBundle:Report/Document:step1.html.twig")
     */
    public function step1Action(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $step = 1;
        $totalSteps = 3;

        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('documents', 'report_documents', 'report_documents_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(FormDir\Report\DocumentType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            /* @var $data EntityDir\Report\Report */
            $data = $form->getData();

            if ('no' === $data->getWishToProvideDocumentation()) {
                if (count($data->getDeputyDocuments()) > 0) {
                    $translator = $this->get('translator');
                    $translatedMessage = $translator->trans('summaryPage.setNoAttemptWithDocuments', [], 'report-documents');
                    $request->getSession()->getFlashBag()->add('error', $translatedMessage);
                } else {
                    $this->getRestClient()->put('report/' . $reportId, $data, ['report','wish-to-provide-documentation']);
                }
            }

            $redirectUrl = 'yes' == $data->getWishToProvideDocumentation()
                ? $this->generateUrl('report_documents', ['reportId' => $report->getId()])
                : $this->generateUrl('report_documents_summary', ['reportId' => $report->getId()]);
            return $this->redirect($redirectUrl);
        }

        return [
            'report'       => $report,
            'step'         => $step,
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/documents/step/2", name="report_documents", defaults={"what"="new"})
     * @Template("AppBundle:Report/Document:step2.html.twig")
     */
    public function step2Action(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, self::$jmsGroups);
        list($nextLink, $backLink) = $this->buildNavigationLinks($report);

        $formAction = $this->generateUrl('report_documents', ['reportId'=>$reportId]);
        $form = $this->createForm(FormDir\Report\UploadType::class, null, ['action' =>  $formAction]);

        if ($request->get('error') == 'tooBig') {
            $message = $this->get('translator')->trans('document.file.errors.maxSizeMessage', [], 'validators');
            $form->get('file')->addError(new FormError($message));
        }

        $form->handleRequest($request);
        if ($form->isValid()) {
            $files = $request->files->get('report_document_upload')['files'];

            try {
                if ($this->allFilesAreVerified($files, $report, $form)) {
                    $this->uploadFiles($files, $report);
                    $request->getSession()->getFlashBag()->add('notice', 'Files uploaded');
                    return $this->redirectToRoute('report_documents', ['reportId' => $reportId]);
                }
            } catch (\Throwable $e) {
                $message = 'Cannot upload file, please try again later';
                $form->get('files')->addError(new FormError($message));
            }
        }

        return [
            'report'   => $report,
            'step'     => $request->get('step'), // if step is set, this is used to show the save and continue button
            'backLink' => $backLink,
            'nextLink' => $nextLink,
            'form'     => $form->createView(),
        ];
    }

    /**
     * @param EntityDir\Report\Report $report
     * @return array
     * @throws \Exception
     */
    private function buildNavigationLinks(EntityDir\Report\Report $report): array
    {
        if (!$report->isSubmitted()) {
            $nextLink = $this->generateUrl('report_documents_summary', ['reportId' => $report->getId(), 'step' => 3, 'from' => 'report_documents']);
            $backLink = $this->generateUrl('documents_step', ['reportId' => $report->getId(), 'step' => 1]);
        } else {
            $nextLink = $this->generateUrl('report_documents_submit_more', ['reportId' => $report->getId(), 'from' => 'report_documents']);
            if ($this->getUser()->isDeputyOrg()) {
                $backLink = $this->generateClientProfileLink($report->getClient());
            } else {
                $backLink = $this->generateUrl('homepage');
            }
        }

        return [$nextLink, $backLink];
    }

    /**
     * @param $files
     * @param EntityDir\Report\Report $report
     * @param \Symfony\Component\Form\FormInterface $form
     * @return bool
     */
    private function allFilesAreVerified($files, EntityDir\Report\Report $report, FormInterface $form): bool
    {
        $verified = true;
        foreach ($files as $file) {
            $document = (new Document())->setFile($file)->setReport($report);
            $verified = $this->container->get('upload_verifier')->verify($document, $form);
        }

        return $verified;
    }

    /**
     * @param $files
     * @param EntityDir\Report\Report $report
     */
    private function uploadFiles($files, EntityDir\Report\Report $report): void
    {
        foreach ($files as $file) {
            $this->uploadFile($file, $report);
        }
    }

    /**
     * @param UploadedFile $file
     * @param EntityDir\Report\Report $report
     */
    private function uploadFile(UploadedFile $file, EntityDir\Report\Report $report): void
    {
        $this
            ->get('file_uploader')
            ->uploadFile($report, file_get_contents($file->getPathName()), $file->getClientOriginalName(), false);
    }

    /**
     * @Route("/report/{reportId}/documents/summary", name="report_documents_summary")
     * @Template("AppBundle:Report/Document:summary.html.twig")
     */
    public function summaryAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getDocumentsState()['state']) {
            return $this->redirectToRoute('documents', ['reportId' => $report->getId()]);
        }

        $fromPage = $request->get('from');

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report'             => $report,
            'backLink'           => $this->generateUrl('report_documents', ['reportId' => $report->getId()]),
            'status'             => $report->getStatus()
        ];
    }

    /**
     * Confirm delete document form
     *
     * @Route("/documents/{documentId}/delete", name="delete_document")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     */
    public function deleteConfirmAction(Request $request, $documentId)
    {
        /** @var EntityDir\Document $document */
        $document = $this->getDocument($documentId);

        if ($document->getReportSubmission() instanceof EntityDir\Report\ReportSubmission) {
            throw new \RuntimeException('Document already submitted and cannot be removed.');
        }

        $this->denyAccessUnlessGranted(DocumentVoter::DELETE_DOCUMENT, $document, 'Access denied');

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
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
     * Removes a document, adds a flash message and redirects to page
     *
     * @return RedirectResponse
     */
    public function deleteDocument(Request $request, $documentId)
    {
        /** @var EntityDir\Report\Document $document */
        $document = $this->getDocument($documentId);

        $report = $document->getReport();
        $this->denyAccessUnlessGranted(DocumentVoter::DELETE_DOCUMENT, $document, 'Access denied');

        try {
            /** @var DocumentService $documentService */
            $documentService = $this->get('AppBundle\Service\DocumentService');
            $result = $documentService->removeDocumentFromS3($document); // rethrows any exception

            if ($result) {
                $request->getSession()->getFlashBag()->add('notice', 'Document has been removed');
            }
        } catch (\Throwable $e) {
            $this->get('logger')->error($e->getMessage());

            $request->getSession()->getFlashBag()->add(
                'error',
                'Document could not be removed. Details: ' . $e->getMessage()
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
     * Confirm additional documents form
     *
     * @Route("/report/{reportId}/documents/submit-more", name="report_documents_submit_more")
     * @Template("AppBundle:Report/Document:submitMoreDocumentsConfirm.html.twig")
     */
    public function submitMoreConfirmAction(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, self::$jmsGroups);

        $fromPage = $request->get('from');

        $backLink = $this->generateUrl('report_documents', ['reportId' => $reportId]);
        $nextLink = $this->generateUrl('report_documents_submit_more_confirmed', ['reportId' => $reportId]);

        return [
            'report'   => $report,
            'backLink' => $backLink,
            'nextLink' => $nextLink,
            'fromPage' => $fromPage
        ];
    }

    /**
     * Confirmed send additional documents.
     *
     * @Route("/report/{reportId}/documents/confirm-submit-more", name="report_documents_submit_more_confirmed")
     * @Template("AppBundle:Report/Document:submitMoreDocumentsConfirmed.html.twig")
     */
    public function submitMoreConfirmedAction(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, self::$jmsGroups);

        // submit the report to generate the submission entry only
        $this->getRestClient()->put('report/' . $report->getId() . '/submit-documents', $report, ['submit']);

        $request->getSession()->getFlashBag()->add('notice', 'The documents attached for your ' . $report->getPeriod() . ' report have been sent to OPG');

        if ($this->getUser()->isDeputyOrg()) {
            return $this->redirect($this->generateClientProfileLink($report->getClient()));
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * Retrieves the document object with required associated entities to populate the table and back links
     *
     * @param $documentId
     * @return mixed
     */
    private function getDocument($documentId)
    {
        return $this->getRestClient()->get(
            'document/' . $documentId,
            'Report\Document',
            ['documents', 'status', 'document-storage-reference', 'document-report-submission', 'document-report', 'report', 'report-client', 'client', 'client-users', 'user-id']
        );
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'documents';
    }
}


