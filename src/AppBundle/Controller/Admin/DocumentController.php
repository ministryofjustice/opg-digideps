<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Model\Email;
use AppBundle\Service\CsvUploader;
use AppBundle\Service\DataImporter\CsvToArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/documents")
 */
class DocumentController extends AbstractController
{
    /**
     * @Route("/list/{what}", name="admin_documents", defaults={"what"="new"})
     * @Template
     */
    public function indexAction(Request $request, $what)
    {
        $archivedParam = ($what == 'new') ? 0 : 1;
        $reports = $this->getRestClient()->get("/document/get-all-with-reports?archived={$archivedParam}", 'Report\\Report[]');

        return [
            'what' => $what,
            'reports' => $reports,
            'countDocuments' => array_sum(array_map(function($report){ return count($report->getDocuments());}, $reports))
        ];
    }

    /**
     * @Route("/download/{reportId}", name="admin_documents_download")
     * @Template
     */
    public function downloadAction(Request $request, $reportId)
    {
        $report = $this->getRestClient()->get("report/{$reportId}/get-documents", 'Report\\Report');

        $s3Storage = $this->get('s3_storage');
        $filename = '/tmp/Report'.$reportId.'_'. date('Y-m-d').'.zip'; //memory too risky, might finish
        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE);
        foreach($report->getDocuments() as $document) {
            $content = $s3Storage->retrieve($document->getStorageReference()); //might throw exception
            $zip->addFromString($document->getFileName() , $content);
        }
        $zip->close();

        $response = new Response();
        //https://perishablepress.com/http-headers-file-downloads/
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires' ,'0');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($filename).'";');
        $response->headers->set('Content-Length', filesize($filename));

        $response->sendHeaders();
        $response->setContent(file_get_contents($filename));

        return $response;
    }

    /**
     * @Route("/archive/{reportId}", name="admin_document_archive")
     * @Template
     */
    public function archiveDocumentsAction(Request $request, $reportId)
    {
        $documentRefs = $this->getRestClient()->put("report/{$reportId}/archive-documents", []);
        foreach($documentRefs as $ref) {
            //$this->get('s3_storage')->delete($ref); // CHECK WHAT'S THE NEEDED LOGIC HERE. could be enough to soft delete and the cron will clean them up
        }

        $request->getSession()->getFlashBag()->add('notice', 'Documents archived');

        return $this->redirectToRoute('admin_documents');
    }

}
