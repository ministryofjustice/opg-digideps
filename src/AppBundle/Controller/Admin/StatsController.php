<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\Report\SubmissionCsvFilter;
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
    /** @var string */
    const API_ENDPOINT = '/report-submission/casrec_data';

    /**
     * @Route("", name="admin_stats")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template
     * @param Request $request
     * @return array|Response
     */
    public function statsAction(Request $request)
    {
        $form = $this->createForm(SubmissionCsvFilterType::class , new SubmissionCsvFilter());
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $csv = $this->generateCsv($form->getData());

                return $this->buildResponse($csv);
            } catch (\Exception $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form'    => $form->createView()
        ];
    }

    /**
     * @param SubmissionCsvFilter $submissionCsvFilter
     * @return mixed
     */
    private function generateCsv(SubmissionCsvFilter $submissionCsvFilter)
    {
        $csvData = $this->getCsvData($submissionCsvFilter);

        return $this->get('csv_generator_service')->generateReportSubmissionsCsv($csvData);
    }

    /**
     * @param SubmissionCsvFilter $submissionCsvFilter
     * @return mixed
     */
    private function getCsvData(SubmissionCsvFilter $submissionCsvFilter)
    {
        return $this->getRestClient()->get($this->generateApiUrl($submissionCsvFilter), 'array');
    }

    /**
     * @param SubmissionCsvFilter $submissionCsvFilter
     * @return string
     */
    private function generateApiUrl(SubmissionCsvFilter $submissionCsvFilter)
    {
        return sprintf (
            '%s?%s',
            self::API_ENDPOINT,
            http_build_query([
                'fromDate' => $submissionCsvFilter->getFromDate(),
                'toDate' => $submissionCsvFilter->getToDate(),
                'orderBy' => $submissionCsvFilter->getOrderBy(),
                'order' => $submissionCsvFilter->getSortOrder()
            ])
        );
    }

    /**
     * @param $csvContent
     * @return Response
     */
    private function buildResponse($csvContent)
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv');

        $attachmentName = sprintf('DD_ReportSubmissions-%s.csv',
            date('Y-m-d')
        );

        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        $response->sendHeaders();

        return $response;
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
