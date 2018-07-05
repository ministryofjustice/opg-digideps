<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Exception\DisplayableException;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\File\MultiDocumentZipFileCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/documents")
 */
class ReportSubmissionController extends AbstractController
{
    const ACTION_DOWNLOAD = 'download';
    const ACTION_ARCHIVE = 'archive';

    /**
     * @Route("/list", name="admin_documents")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
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
        $reportSubmissions = [];

        try {
            foreach ($checkedBoxes as $reportSubmissionId) {
                $reportSubmissions[] = $this->getRestClient()->get("/report-submission/{$reportSubmissionId}", 'Report\\ReportSubmission');
            }
            $zipFileCreator = new MultiDocumentZipFileCreator($this->get('s3_storage'), $reportSubmissions);
            $filename = $zipFileCreator->createZipFile();

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

            $zipFileCreator->cleanUp();

            return $response;
        } catch (\Exception $e) {
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
        return [
            'q'      => $request->get('q'),
            'status' => $request->get('status', 'new'), // new | archived
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
            'created_by_role'   => $request->get('created_by_role'),
            'orderBy'           => $request->get('orderBy', 'createdOn'),
            'order'             => $request->get('order', 'ASC')
        ];
    }

    /**
     * @Route("/dd-report-submissions.csv", name="admin_report_submissions_csv")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function reportSubmissionsCsvAction(Request $request)
    {
        try {

            $currentFilters = self::getFiltersFromRequest($request);
            $ret = $this->getRestClient()->get(
                '/report-submission/casrec_data?' . http_build_query($currentFilters),
                'array'
                );

            $records = $this->getRestClient()->arrayToEntities(EntityDir\Report\ReportSubmission::class . '[]', $ret['records']);

        } catch (\Exception $e) {
            throw new DisplayableException($e);
        }

        $csvContent = $this->get('csv_generator_service')->generateReportSubmissionsCsv($records);

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');

        $attachmentName = sprintf('DD_ReportSubmissions-%s.csv',
            date('Y-m-d')
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        // Send headers before outputting anything
        $response->sendHeaders();

        return $response;
    }
}
