<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\DisplayableException;
use App\Form\Admin\ReportSubmissionDownloadFilterType;
use App\Form\Admin\SatisfactionFilterType;
use App\Form\Admin\StatPeriodType;
use App\Mapper\ReportSatisfaction\ReportSatisfactionSummaryMapper;
use App\Mapper\ReportSatisfaction\ReportSatisfactionSummaryQuery;
use App\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use App\Mapper\ReportSubmission\ReportSubmissionSummaryQuery;
use App\Service\Client\Internal\StatsApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Csv\ActiveLaysCsvGenerator;
use App\Service\Csv\SatisfactionCsvGenerator;
use App\Transformer\ReportSubmission\ReportSubmissionBurFixedWidthTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/stats")
 */
class StatsController extends AbstractController
{
    private RestClient $restClient;
    private SatisfactionCsvGenerator $csvGenerator;
    private StatsApi $statsApi;
    private ActiveLaysCsvGenerator $activeLaysCsvGenerator;

    public function __construct(
        RestClient $restClient,
        SatisfactionCsvGenerator $csvGenerator,
        StatsApi $statsApi,
        ActiveLaysCsvGenerator $activeLaysCsvGenerator
    ) {
        $this->restClient = $restClient;
        $this->csvGenerator = $csvGenerator;
        $this->statsApi = $statsApi;
        $this->activeLaysCsvGenerator = $activeLaysCsvGenerator;
    }

    /**
     * @Route("", name="admin_stats")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Stats/stats.html.twig")
     *
     * @param Request $request
     * @param ReportSubmissionSummaryMapper $mapper
     * @param ReportSubmissionBurFixedWidthTransformer $transformer
     *
     * @return array|Response
     */
    public function statsAction(Request $request, ReportSubmissionSummaryMapper $mapper, ReportSubmissionBurFixedWidthTransformer $transformer)
    {
        $form = $this->createForm(ReportSubmissionDownloadFilterType::class, new ReportSubmissionSummaryQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSubmissionSummaries = $mapper->getBy($form->getData());
                $downloadableData = $transformer->transform($reportSubmissionSummaries);

                return $this->buildResponse($downloadableData);
            } catch (\Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/satisfaction", name="admin_satisfaction")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Stats/satisfaction.html.twig")
     * @param Request $request
     * @param ReportSatisfactionSummaryMapper $mapper
     * @return array|Response
     */
    public function satisfactionAction(Request $request, ReportSatisfactionSummaryMapper $mapper)
    {
        $form = $this->createForm(SatisfactionFilterType::class, new ReportSatisfactionSummaryQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSatisfactionSummaries = $mapper->getBy($form->getData());
                $csv = $this->csvGenerator->generateSatisfactionResponsesCsv($reportSatisfactionSummaries);

                $response = new Response($csv);

                $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'satisfaction.csv'
                );

                $response->headers->set('Content-Disposition', $disposition);
                return $response;
            } catch (\Throwable $e) {
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

        $attachmentName = sprintf('cwsdigidepsopg00001%s.dat', date('YmdHi'));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        $response->sendHeaders();

        return $response;
    }

    /**
     * @Route("/metrics", name="admin_metrics")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Stats/metrics.html.twig")
     * @param Request $request
     * @return array|Response
     */
    public function metricsAction(Request $request)
    {
        $form = $this->createForm(StatPeriodType::class);
        $form->handleRequest($request);

        $append = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $append = "&startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
        }

        $metrics = ['satisfaction', 'reportsSubmitted', 'clients', 'registeredDeputies'];

        foreach ($metrics as $metric) {
            $all = $this->restClient->get('stats?metric=' . $metric . $append, 'array');
            $byRole = $this->restClient->get('stats?metric=' . $metric . '&dimension[]=deputyType' . $append, 'array');

            $stats[$metric] = array_merge(
                ['all' => $all[0]['amount']],
                $this->mapToDeputyType($byRole)
            );
        }

        return [
            'stats' => $stats,
            'form' => $form->createView()
        ];
    }

    /**
     * Map an array of metric responses to be addressible by deputyType
     *
     * @param array $result
     *
     * @return array
     */
    private function mapToDeputyType(array $result): array
    {
        $resultByDeputyType = [];

        foreach ($result as $resultBit) {
            $resultByDeputyType[$resultBit['deputyType']] = $resultBit['amount'];
        }

        return $resultByDeputyType;
    }

    /**
     * @Route("/downloadActiveLaysCsv", name="admin_active_lays_csv")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @return Response
     */
    public function downloadActiveLayCsv()
    {
        $activeLaysData = $this->statsApi->getActiveLayReportData();
        $csv = $this->activeLaysCsvGenerator->generateActiveLaysCsv($activeLaysData);

        $response = new Response($csv);

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'activeLays.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
