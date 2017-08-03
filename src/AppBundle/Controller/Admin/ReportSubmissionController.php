<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        $currentFilters = self::getFiltersFromRequest($request);
        $ret = $this->getRestClient()->get('/report-submission?' . http_build_query($currentFilters), 'array');
        $records = $this->getRestClient()->arrayToEntities(EntityDir\Report\ReportSubmission::class . '[]', $ret['records']);

        return [
            'filters' => $currentFilters,
            'records' => $records,
            'counts'  => [
                'new'      => $ret['counts']['new'],
                'archived' => $ret['counts']['archived'],
            ],
        ];
    }

    /**
     * @Route("/download/{reportSubmissionId}", name="admin_documents_download")
     * @Template
     */
    public function downloadAction(Request $request, $reportSubmissionId)
    {
        try {
            /* @var $reportSubmission EntityDir\Report\ReportSubmission */
            $reportSubmission = $this->getRestClient()->get("/report-submission/{$reportSubmissionId}", 'Report\\ReportSubmission');

            // store files locally, for subsequent memory-less ZIP creation
            $s3Storage = $this->get('s3_storage');
            $filesToAdd = [];
            if (empty($reportSubmission->getDocuments())) {
                throw new \RuntimeException('No documents found for downloading');
            }
            foreach ($reportSubmission->getDocuments() as $document) {
                $content = $s3Storage->retrieve($document->getStorageReference()); //might throw exception
                $dfile = '/tmp/DDDocument' . $document->getId() . microtime(1);
                file_put_contents($dfile, $content);
                unset($content);
                $filesToAdd[$document->getFileName()] = $dfile;
            }

            // create ZIP files and add previously-stored uploaded documents
            $filename = '/tmp/' . $reportSubmission->getZipName();
            $zip = new \ZipArchive();
            $zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE | \ZipArchive::CHECKCONS);
            foreach ($filesToAdd as $localname => $filePath) {
                $zip->addFile($filePath, $localname); // addFromString crashes
            }
            $zip->close();
            unset($zip);

            // clean up
            foreach ($filesToAdd as $f) {
                unlink($f);
            }

            // send ZIP to user
            $response = new Response();
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response->headers->set('Expires', '0');
            $response->headers->set('Content-type', 'application/octet-stream');
            $response->headers->set('Content-Description', 'File Transfer');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
            // currently disabled as behat goutte driver gets a corrupted file with this setting
            //$response->headers->set('Content-Length', filesize($filename));
            $response->sendHeaders();
            $response->setContent(readfile($filename));

            unlink($filename);

            return $response;
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('error', 'Cannot download documents. Details: ' . $e->getMessage());

            return $this->redirectToRoute('admin_documents');
        }
    }

    /**
     * Note: archive won't delete documents, a cron [https://opgtransform.atlassian.net/browse/DDPB-1474] will do that
     *
     * @Route("/archive/{reportSubmissionId}", name="admin_document_archive")
     * @Template
     */
    public function archiveDocumentsAction(Request $request, $reportSubmissionId)
    {
        $this->getRestClient()->put("report-submission/{$reportSubmissionId}", ['archive'=>true]);

        $request->getSession()->getFlashBag()->add('notice', 'Documents archived');

        $filtersToPass = array_filter(self::getFiltersFromRequest($request));

        return $this->redirectToRoute('admin_documents', $filtersToPass);
    }

    /**
     * @param  Request $request
     * @return array
     */
    private static function getFiltersFromRequest(Request $request)
    {
        return [
            'q'      => $request->get('q'),
            'status' => $request->get('status', 'new'), // new | archived
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
            'created_by_role'   => $request->get('created_by_role'),
        ];
    }
}
