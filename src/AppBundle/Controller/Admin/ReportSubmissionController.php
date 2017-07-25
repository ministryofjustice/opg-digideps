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
class ReportSubmissionController extends AbstractController
{
    /**
     * @Route("/list", name="admin_documents")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $currentFilters = [
            'q'      => $request->get('q'),
            'status' => $request->get('status', 'new') // new | archived
        ];
        $reportSubmissions = $this->getRestClient()->get('/report-submission?' . http_build_query($currentFilters), 'Report\\ReportSubmission[]');

        return [
            'filters' => $currentFilters,
            'reportSubmissions' => $reportSubmissions,
            'countDocuments' => array_sum(array_map(function($report){ return count($report->getDocuments());}, $reportSubmissions))
        ];
    }

    /**
     * @Route("/download/{reportSubmissionId}", name="admin_documents_download")
     * @Template
     */
    public function downloadAction(Request $request, $reportSubmissionId)
    {
        $reportSubmission = $this->getRestClient()->get("/report-submission/{$reportSubmissionId}", 'Report\\ReportSubmission');

        // store files locally, for subsequent memory-less ZIP creation
        $s3Storage = $this->get('s3_storage');
        $filesToAdd = [];
        foreach(array_slice($reportSubmission->getDocuments(),0,99) as $document) {
            $content = $s3Storage->retrieve($document->getStorageReference()); //might throw exception
            $dfile = '/tmp/DDDocument'.$document->getId().microtime(1);
            file_put_contents($dfile, $content);
            unset($content);
            $filesToAdd[$document->getFileName()] = $dfile;
        }

        // create ZIP files and add previously-stored uploaded documents
        $filename = '/tmp/Report'.$reportSubmission->getReport()->getId().'_'. date('Y-m-d').'.zip'; //memory too risky
        $zip = new \ZipArchive();
        $zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE | \ZipArchive::CHECKCONS);
        foreach($filesToAdd as $localname => $filePath) {
            $zip->addFile($filePath, $localname); // addFromString crashes
        }
        $zip->close();
        unset($zip);

        // clean up
        foreach($filesToAdd as $f) {
            unlink($f);
        }

        // send ZIP to user
        $response = new Response();
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires' ,'0');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.basename($filename).'";');
        $response->headers->set('Content-Length', filesize($filename));
        $response->sendHeaders();
        $response->setContent(readfile($filename));

        unlink($filename);

        return $response;
    }

    /**
     * @Route("/archive/{reportSubmissionId}", name="admin_document_archive")
     * @Template
     */
    public function archiveDocumentsAction(Request $request, $reportSubmissionId)
    {
        $documentRefs = $this->getRestClient()->put("report-submission/{$reportSubmissionId}/archive", []);
        foreach($documentRefs as $ref) {
            //$this->get('s3_storage')->delete($ref); // CHECK WHAT'S THE NEEDED LOGIC HERE. could be enough to soft delete and the cron will clean them up
        }

        $request->getSession()->getFlashBag()->add('notice', 'Documents archived');

        return $this->redirectToRoute('admin_documents');
    }

}
