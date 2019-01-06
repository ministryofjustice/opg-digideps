<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Exception\DisplayableException;
use AppBundle\Form\Admin\ReportSubmissionDownloadFilterType;
use AppBundle\Mapper\ReportSubmission\ReportSubmissionSummaryQuery;
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
     * @param Request $request
     * @return array|Response
     */
    public function statsAction(Request $request)
    {
        $form = $this->createForm(ReportSubmissionDownloadFilterType::class , new ReportSubmissionSummaryQuery());
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {

                $mapper = $this->get('mapper.report_submission_summary_mapper');
                $transformer = $this->get('transformer.report_submission_bur_fixed_width_transformer');

                $reportSubmissionSummaries = $mapper->getBy($form->getData());
                $downloadableData = $transformer->transform($reportSubmissionSummaries);

                return $this->buildResponse($downloadableData);

            } catch (\Exception $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @param $csvContent
     * @return Response
     */
    private function buildResponse($csvContent)
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $attachmentName = sprintf('DD_ReportSubmissions-%s.dat', date('Y-m-d'));
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
