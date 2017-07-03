<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Document as Document;
use AppBundle\Form as FormDir;

use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $report->setDocuments([
            new Document('file1.jpg', new \DateTime('now'), Document::TYPE_PDF),
            new Document('file2.jpg', new \DateTime('now'), Document::TYPE_PDF),
            new Document('file3.jpg', new \DateTime('now'), Document::TYPE_PDF),
        ]);


        if ($request->getMethod() === 'POST') {
            file_put_contents('/tmp/file1.txt', 'CONTENT'); //TMP
            if ($fileUploader->uploadFile($report, 'file1.jpg', '/tmp/file1.txt')) {
                $request->getSession()->getFlashBag()->add('notice', 'File uploaded');
                $this->redirectToRoute('report_documents', ['reportId'=>$reportId]);
            }

            //TODO attach virus errors
        }

        return [
            'report' => $report,
        ];
    }

}
