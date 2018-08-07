<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\SubmissionCsvFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/stats")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("", name="admin_stats")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function statsAction(Request $request)
    {
        $filters = [
            'order_by'    => 'id',
            'sort_order'  => 'DESC',
        ];

        // This form is for the submissions CSV only and its filters are fromDate, order_by and sort_order
        $form = $this->createForm(SubmissionCsvFilterType::class , null);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $filters = $form->getData() + $filters;

            try {
                $ret = $this->getRestClient()->get(
                    '/report-submission/casrec_data?' . http_build_query($filters),
                    'array'
                );

                $records = $this->getRestClient()->arrayToEntities(ReportSubmission::class . '[]', $ret['records']);

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
            } catch (\Exception $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form'    => $form->createView(),
            'filters' => $filters
        ];
    }

    /**
     * @Route("/dd-stats.csv", name="admin_stats_csv")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     */
    public function statsCsvAction(Request $request)
    {
        try {
            $regenerate = $request->get('regenerate') ? 1 : 0;
            $rawCsv = (string) $this->getRestClient()->get("casrec/stats.csv?regenerate=$regenerate", 'raw');
        } catch (\Exception $e) {
            throw new DisplayableException($e);
        }
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'plain/text');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="dd-stats.' . date('Y-m-d') . '.csv";');
        $response->sendHeaders();
        $response->setContent($rawCsv);

        return $response;
    }
}
