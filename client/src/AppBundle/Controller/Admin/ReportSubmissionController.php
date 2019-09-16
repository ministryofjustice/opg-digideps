<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\DocumentDownloader;
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
    
    /**
     * @var DocumentDownloader
     */
    private $documentDownloader;

    public function __construct(DocumentDownloader $documentDownloader)
    {
        $this->documentDownloader = $documentDownloader;
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
                        $ret = $this->documentDownloader->processDownload($request, $checkedBoxes);
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
}
