<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Document as Document;
use AppBundle\Form as FormDir;

use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

class DocumentController extends AbstractController
{
    private static $jmsGroups = [
        'document',
    ];

    /**
     * @Route("/report/{reportId}/documents", name="report_documents")
     * @Template()
     */
    public function indexAction(Request $request, $reportId)
    {
        $fileUploader = $this->get('file_uploader');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // fake documents. remove when the upload is implemented
        $document = new Document(null, new DateTime(), null);
        $report->setDocuments([
            new Document('file1.jpg', new \DateTime('now'), Document::TYPE_PDF),
            new Document('file2.jpg', new \DateTime('now'), Document::TYPE_PDF),
            new Document('file3.jpg', new \DateTime('now'), Document::TYPE_PDF),
        ]);


        $form = $this->createForm(FormDir\Report\DocumentUploadType::class, $document);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $document->getFileName(); /* @var $uploadedFile UploadedFile*/
            $fileUploader->uploadFile($report, $uploadedFile->getClientOriginalName(), $uploadedFile->getPathname());

            $request->getSession()->getFlashBag()->add('notice', 'File uploaded');
            $this->redirectToRoute('report_documents', ['reportId'=>$reportId]);

        }

        return [
            'report' => $report,
            'backLink' => $this->generateUrl('report_overview', ['reportId' => $report->getId()]),
            'form' => $form->createView(),
        ];
    }

}
