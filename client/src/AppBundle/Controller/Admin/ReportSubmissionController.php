<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\DocumentService;
use AppBundle\Service\File\DocumentsZipFileCreator;
use AppBundle\Service\ReportSubmissionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ReportSubmissionController extends AbstractController
{
    const ACTION_DOWNLOAD = 'download';
    const ACTION_ARCHIVE = 'archive';
    const MSG_NOT_DOWNLOADABLE = 'This report is not downloadable';

    /**
     * @var DocumentService
     */
    private $documentService;

    /**
     * @var ReportSubmissionService
     */
    private $reportSubmissionService;

    public function __construct(DocumentService $documentService, ReportSubmissionService $reportSubmissionService)
    {
        $this->documentService = $documentService;
        $this->reportSubmissionService = $reportSubmissionService;
    }

    /**
     * @Route("/documents/list", name="admin_documents")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/ReportSubmission:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $ret = $this->processPost($request);
        if ($ret instanceof Response) {
            return $ret;
        }
        $currentFilters = self::getFiltersFromRequest($request);
        $ret = $this->getRestClient()->get('/report-submission?' . http_build_query($currentFilters), 'array');

        $records = $this->getRestClient()->arrayToEntities(EntityDir\Report\ReportSubmission::class . '[]', $ret['records']);

        $nOfdownloadableSubmissions = count(array_filter($records, function ($s) {
            return $s->isDownloadable();
        }));

        $isNewPage = $currentFilters['status'] == 'new';

        return [
            'filters' => $currentFilters,
            'records' => $records,
            'postActions' => $isNewPage ? [
                self::ACTION_DOWNLOAD,
                self::ACTION_ARCHIVE,
            ] : [self::ACTION_DOWNLOAD],
            'counts'  => [
                'new'      => $ret['counts']['new'],
                'archived' => $ret['counts']['archived'],
            ],
            'nOfdownloadableSubmissions' => $nOfdownloadableSubmissions,
            'isNewPage' => $isNewPage,
        ];
    }

    /**
     * Process a post
     *
     * @param Request $request request
     *
     */
    private function processPost(Request $request)
    {
        if ($request->isMethod('POST')) {
            if (empty($request->request->get('checkboxes'))) {
                $request->getSession()->getFlashBag()->add('error', 'Please select at least one report submission');
                return;
            }

            $checkedBoxes = array_keys($request->request->get('checkboxes'));
            $action = strtolower($request->request->get('multiAction'));

            if (in_array($action, [self::ACTION_DOWNLOAD,self::ACTION_ARCHIVE])) {
                $totalChecked = count($checkedBoxes);

                switch ($action) {
                    case self::ACTION_ARCHIVE:
                        $this->processArchive($checkedBoxes);
                        $notice = $this->get('translator')->transChoice(
                            'page.postactions.archived.notice',
                            $totalChecked,
                            ['%count%' => $totalChecked],
                            'admin-documents'
                            );

                        $request->getSession()->getFlashBag()->add('notice', $notice);
                        break;

                    case self::ACTION_DOWNLOAD:
                        $ret = $this->processDownload($request, $checkedBoxes);
                        if ($ret instanceof Response) {
                            return $ret;
                        }
                        break;
                }
            }
        }
    }

    /**
     * Archive multiple documents based on the supplied ids
     *
     * @param array $checkedBoxes ids selected by the user
     *
     */
    private function processArchive($checkedBoxes)
    {
        foreach ($checkedBoxes as $reportSubmissionId) {
            $this->getRestClient()->put("report-submission/{$reportSubmissionId}", ['archive'=>true]);
        }
    }

    /**
     * Download multiple documents based on the supplied ids
     *
     * @param Request $request      request
     * @param array   $checkedBoxes ids selected by the user
     *
     * @return Response
     */
    private function processDownload(Request $request, $checkedBoxes)
    {
        try {
            $zipFiles = [];
            $missingDocuments = [];
            $zipFileCreator = new DocumentsZipFileCreator();
            $caseNumber = '';

            foreach ($checkedBoxes as $reportSubmissionId) {
                /** @var EntityDir\Report\ReportSubmission $reportSubmission */
                $reportSubmission = $this->reportSubmissionService->getReportSubmissionById($reportSubmissionId);

                if ($reportSubmission->isDownloadable() !== true) {
                    throw new \RuntimeException(self::MSG_NOT_DOWNLOADABLE);
                }

                if (empty($reportSubmission->getDocuments())) {
                    throw new \RuntimeException('No documents found for downloading');
                }

                [$documents, $missing] = $this->documentService->retrieveDocumentsFromS3ByReportSubmission($reportSubmission);
                $zipFiles[] = $zipFileCreator->createZipFileFromDocumentContents($documents, $reportSubmission);

                if (!empty($missing)) {
                    foreach($missing as $missingDoc) {
                        $missingDocuments[] = $missingDoc;
                    }
                    $caseNumber = $reportSubmission->getReport()->getClient()->getCaseNumber();
                }
            }

            $fileName = $zipFileCreator->createMultiZipFile($zipFiles);

            // send ZIP to user
            $response = self::generateDownloadResponse($fileName);

            $zipFileCreator->cleanUp();

            if (!empty($missingDocuments)) {
                $flashMessage = self::createMissingDocumentsFlashMessage($missingDocuments, $caseNumber);
                $request->getSession()->getFlashBag()->add('error', $flashMessage);
            }

            return $response;
        } catch (\Throwable $e) {
            if ($zipFileCreator instanceof $zipFileCreator) {
                $zipFileCreator->cleanUp();
            }
            $request->getSession()->getFlashBag()->add('error', 'Cannot download documents. Details: ' . $e->getMessage());
        }
    }

    /**
     * @param  Request $request
     * @return array
     */
    private static function getFiltersFromRequest(Request $request)
    {
        $order = $request->get('status', 'new') === 'new' ? 'ASC' : 'DESC';

        return [
            'q'      => $request->get('q'),
            'status' => $request->get('status', 'new'), // new | archived
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
            'created_by_role'   => $request->get('created_by_role'),
            'orderBy'           => $request->get('orderBy', 'createdOn'),
            'order'             => $request->get('order', $order),
            'fromDate'          => $request->get('fromDate')
        ];
    }

    private static function createMissingDocumentsFlashMessage(array $missingDocuments, string $caseNumber)
    {
        $missingDocumentBullets = '<ul><li>' .  implode('</li><li>', $missingDocuments) . '</li></ul>';
        return <<<FLASH
The following documents for case number $caseNumber could not be downloaded:
$missingDocumentBullets
FLASH;
    }

    private static function generateDownloadResponse(string $fileName)
    {
        $response = new Response();
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Expires', '0');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Description', 'File Transfer');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($fileName) . '";');
        // currently disabled as behat goutte driver gets a corrupted file with this setting
        //$response->headers->set('Content-Length', filesize($filename));
        $response->sendHeaders();
        $response->setContent(readfile($fileName));
        return $response;
    }
}
