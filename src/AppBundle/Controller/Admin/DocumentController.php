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
     * @Route("/download/{id}", name="admin_document_download")
     * @Template
     */
    public function downloadAction(Request $request, $id)
    {
        $document = $this->getRestClient()->get("document/{$id}", 'Report\\Document', [
            'documents', 'document-storage-reference'
        ]);
        if (!$document) {
            return $this->createNotFoundException("Cannot find file");
        }
        $content = $this->get('s3_storage')->retrieve($document->getStorageReference()); //might throw exception

        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
//        $response->headers->set('Content-type', 'plain/text');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="file.pdf";');
        $response->sendHeaders();
        $response->setContent($content);

        return $response;
    }

    /**
     * @Route("/delete/{id}", name="admin_document_delete")
     * @Template
     */
    public function deleteAction(Request $request, $id)
    {
        $document = $this->getRestClient()->get("document/{$id}", 'Report\\Document', [
            'documents', 'document-storage-reference'
        ]);
        if (!$document) {
            return $this->createNotFoundException("Cannot find file");
        }
        $this->get('s3_storage')->delete($document->getStorageReference()); //might throw exception
        $this->getRestClient()->delete("document/{$id}");

        return new Response('file deleted OK');
    }

    /**
     * @Route("/archive/{reportId}", name="admin_document_archive")
     * @Template
     */
    public function archiveDocumentsAction(Request $request, $reportId)
    {
        $documentRefs = $this->getRestClient()->put("report/{$reportId}/archive-documents", []);
        foreach($documentRefs as $ref) {
            //$this->get('s3_storage')->delete($ref); // CHECK WHAT'S THE NEEDED LOGIC HERE
        }

        $request->getSession()->getFlashBag()->add('notice', 'Documents archived');

        return $this->redirectToRoute('admin_documents');
    }

}
