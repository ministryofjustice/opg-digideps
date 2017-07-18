<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Document as Document;
use AppBundle\Form as FormDir;

use AppBundle\Service\File\Checker\Exception\RiskyFileException;
use AppBundle\Service\File\Checker\Exception\VirusFoundException;
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
        'documents'
    ];

    /**
     * @Route("/report/{reportId}/documents/{what}", name="report_documents", defaults={"what"="new"})
     * @Template()
     */
    public function indexAction(Request $request, $reportId, $what = 'new')
    {
        $fileUploader = $this->get('file_uploader');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

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

            /** @var UploadableFileInterface $fileToStore */
            //$fileToStore = $this->getUploadFileFactory()->createFileToStore($uploadedFile);

            try {
//                $fileToStore->checkFile();
//                if ($fileToStore->isSafe())
//                {
                    $fileUploader->uploadFile($report, $uploadedFile);
                    $request->getSession()->getFlashBag()->add('notice', 'File uploaded');
//                } else {
//                    $request->getSession()->getFlashBag()->add('notice', 'File could not be uploaded');
//                }

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
            'backLink' => $this->generateUrl('report_overview', ['reportId' => $report->getId()]),
            'form'     => $form->createView(),
        ];
    }



}
