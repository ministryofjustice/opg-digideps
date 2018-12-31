<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Dto\ReportSubmissionDownloadFilterDto;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportSubmissionDownloadFilterType;
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
        $form = $this->createForm(ReportSubmissionDownloadFilterType::class , new ReportSubmissionDownloadFilterDto());
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
     * @param ReportSubmissionDownloadFilterDto $downloadFilterDto
     * @return mixed
     */
    private function generateCsv(ReportSubmissionDownloadFilterDto $downloadFilterDto)
    {
        $csvData = $this->getCsvData($downloadFilterDto);

        return $this->get('csv_generator_service')->generateReportSubmissionsCsv($csvData);
    }

    /**
     * @param ReportSubmissionDownloadFilterDto $downloadFilterDto
     * @return mixed
     */
    private function getCsvData(ReportSubmissionDownloadFilterDto $downloadFilterDto)
    {
        return $this->getRestClient()->get($this->generateApiUrl($downloadFilterDto), 'array');
    }

    /**
     * @param ReportSubmissionDownloadFilterDto $downloadFilterDto
     * @return string
     */
    private function generateApiUrl(ReportSubmissionDownloadFilterDto $downloadFilterDto)
    {
        return sprintf (
            '%s?%s',
            self::API_ENDPOINT,
            http_build_query([
                'fromDate' => $downloadFilterDto->getFromDate(),
                'toDate' => $downloadFilterDto->getToDate(),
                'orderBy' => $downloadFilterDto->getOrderBy(),
                'order' => $downloadFilterDto->getSortOrder()
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
