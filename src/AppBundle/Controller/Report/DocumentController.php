<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Document as Document;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
use AppBundle\Service\File\Checker\FileCheckerInterface;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\File\Types\UploadableFileInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends AbstractController
{
    private static $jmsGroups = [
        'report-documents',
        'documents',
        'documents-state',
    ];

    /**
     * @Route("/report/{reportId}/documents", name="documents")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getDocumentsState()['state'] !== EntityDir\Report\Status::STATE_NOT_STARTED) {
            $referer = $request->headers->get('referer');
            $redirectResponse = false !== strpos($referer, '/step/1')
                ? $this->redirectToRoute('report_overview', ['reportId' => $reportId])
                : $this->redirectToRoute('documents_step' , ['reportId' => $reportId, 'step' => 1]);
            return $redirectResponse;
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/documents/step/{step}", name="documents_step")
     * @Template()
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $totalSteps = 3;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('report_documents_summary', ['reportId' => $reportId]);
        }
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getWishToProvideDocumentation() === 'no' && $step > 1) {
            return $this->redirectToRoute('report_documents_summary', ['reportId' => $reportId]);
        }

        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector()
            ->setRoutes('documents', 'report_documents', 'report_documents_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(new FormDir\Report\DocumentType($this->get('translator')), $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            /* @var $data EntityDir\Report\Report */
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['report','wish-to-provide-documentation']);

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'report'       => $report,
            'step'         => $step,
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => '',
        ];
    }

    /**
     * @Route("/report/{reportId}/documents", name="report_documents", defaults={"what"="new"})
     * @Template()
     */
    public function indexAction(Request $request, $reportId)
    {
        $fileUploader = $this->get('file_uploader');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getWishToProvideDocumentation() === 'no') {
            return $this->redirectToRoute('report_documents_summary', ['reportId' => $reportId]);
        }

        // fake documents. remove when the upload is implemented
        $document = new Document();
        $document->setReport($report);
        $form = $this->createForm(FormDir\Report\DocumentUploadType::class, $document, [
            'action' => $this->generateUrl('report_documents', ['reportId'=>$reportId]) //needed to reset possible JS errors
        ]);
        if ($request->get('error') == 'tooBig') {
            $message = $this->get('translator')->trans('document.file.errors.maxSizeMessage', [], 'validators');
            $form->get('file')->addError(new FormError($message));
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            /* @var $uploadedFile UploadedFile */
            $uploadedFile = $document->getFile();

            /** @var FileCheckerInterface $fileChecker */
            $fileChecker = $this->get('file_checker_factory')->factory($uploadedFile);

            try {
                $fileChecker->checkFile();
                if ($fileChecker->isSafe()) {
                    $fileUploader->uploadFile(
                        $report->getId(),
                        file_get_contents($uploadedFile->getPathName()),
                        $uploadedFile->getClientOriginalName()
                    );
                    $request->getSession()->getFlashBag()->add('notice', 'File uploaded');
                } else {
                    $request->getSession()->getFlashBag()->add('notice', 'File could not be uploaded');
                }

                return $this->redirectToRoute('report_documents', ['reportId' => $reportId]);
            } catch (\Exception $e) {
                $errorToErrorTranslationKey = [
                    RiskyFileException::class => 'risky',
                    VirusFoundException::class => 'virusFound',
                ];
                $errorClass = get_class($e);
                if (isset($errorToErrorTranslationKey[$errorClass])) {
                    $errorKey = $errorToErrorTranslationKey[$errorClass];
                } else {
                    $errorKey = 'generic';
                }
                $message = $this->get('translator')->trans("document.file.errors.{$errorKey}", [
                    '%techDetails%' => $this->getParameter('kernel.debug') ? $e->getMessage() : $request->headers->get('x-request-id'),
                ], 'validators');
                $form->get('file')->addError(new FormError($message));
                $this->get('logger')->error($e->getMessage()); //fully log exceptions
            }
        }

        return [
            'report'   => $report,
            'step'     => $request->get('step'), // if step is set, this is used to show the save and continue button
            'backLink' => $this->generateUrl('report_overview', ['reportId' => $report->getId()]),
            'nextLink' => $this->generateUrl('report_documents_summary', ['reportId' => $report->getId(), 'step' => 3, 'from' => 'report_documents']),
            'form'     => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/documents/summary", name="report_documents_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

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
     * @Template("AppBundle:Report/Document:deleteConfirm.html.twig")
     */
    public function deleteConfirmAction(Request $request, $documentId, $confirmed = false)
    {
        /** @var EntityDir\Document $document */
        $document = $this->getDocument($documentId);

        $this->denyAccessUnlessGranted('delete-document', $document, 'Access denied');

        return [
            'report'  => $document->getReport(),
            'document' => $document,
            'backLink' => $this->generateUrl('report_documents', ['reportId' => $document->getReport()->getId()])
        ];
    }

    /**
     * Removes a document, adds a flash message and redirects to page
     *
     * @Route("/document/{documentId}/delete/confirm", name="delete_document_confirm")
     * @Template()
     */
    public function deleteConfirmedAction(Request $request, $documentId)
    {
        try {
            /** @var EntityDir\Document $document */
            $document = $this->getDocument($documentId);

            $this->denyAccessUnlessGranted('delete-document', $document, 'Access denied');

            $this->getRestClient()->delete('document/' . $documentId);

            $request->getSession()->getFlashBag()->add('notice', 'Document has been removed');
        } catch (\Exception $e) {
            $this->get('logger')->error($e->getMessage());

            $request->getSession()->getFlashBag()->add(
                'error',
                'Document could not be removed'
            );
        }

        return $this->redirect($this->generateUrl('report_documents', ['reportId' => $document->getReport()->getId()]));
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
            ['documents', 'status', 'document-report', 'report', 'client', 'user']
        );
    }
}
