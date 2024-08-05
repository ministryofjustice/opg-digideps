<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\DisplayableException;
use App\Form\Admin\BenefitsMetricsFilterType;
use App\Form\Admin\ImbalanceMetricsFilterType;
use App\Form\Admin\InactiveAdminReportFilterType;
use App\Form\Admin\ReportSubmissionDownloadFilterType;
use App\Form\Admin\SatisfactionFilterType;
use App\Form\Admin\StatPeriodType;
use App\Form\Admin\UserResearchResponseFilterType;
use App\Mapper\DateRangeQuery;
use App\Mapper\ReportSatisfaction\ReportSatisfactionSummaryMapper;
use App\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use App\Mapper\UserResearchResponse\UserResearchResponseSummaryMapper;
use App\Service\Client\Internal\StatsApi;
use App\Service\Client\RestClient;
use App\Service\Csv\ActiveLaysCsvGenerator;
use App\Service\Csv\AssetsTotalsCSVGenerator;
use App\Service\Csv\ClientBenefitMetricsCsvGenerator;
use App\Service\Csv\InactiveAdminUsersCsvGenerator;
use App\Service\Csv\ReportImbalanceCsvGenerator;
use App\Service\Csv\SatisfactionCsvGenerator;
use App\Service\Csv\UserResearchResponseCsvGenerator;
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
    public function __construct(
        private readonly RestClient $restClient,
        private readonly SatisfactionCsvGenerator $satisfactionCsvGenerator,
        private readonly StatsApi $statsApi,
        private readonly ActiveLaysCsvGenerator $activeLaysCsvGenerator,
        private readonly UserResearchResponseCsvGenerator $userResearchResponseCsvGenerator,
        private readonly AssetsTotalsCSVGenerator $assetsTotalsCSVGenerator,
        private readonly ClientBenefitMetricsCsvGenerator $clientBenefitMetricsCsvGenerator,
        private readonly InactiveAdminUsersCsvGenerator $inactiveAdminUserCsvGenerator,
        private readonly ReportImbalanceCsvGenerator $reportImbalanceCsvGenerator
    ) {
    }

    /**
     * @Route("", name="admin_stats")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Stats/stats.html.twig")
     */
    public function stats(
        Request $request,
        ReportSubmissionSummaryMapper $mapper,
        ReportSubmissionBurFixedWidthTransformer $transformer
    ): array|Response {
        $form = $this->createFilterTypeForm($request, ReportSubmissionDownloadFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSubmissionSummaries = $mapper->getBy($form->getData());

                $startTime = microtime(true);
                $downloadableData = $transformer->transform($reportSubmissionSummaries);
                $endTime = microtime(true);
                file_put_contents('php://stderr', print_r('TransformerClient: '.($endTime - $startTime).' ms ', true));

                $startTime = microtime(true);
                $builtResponse = $this->buildResponse($downloadableData);
                $endTime = microtime(true);
                file_put_contents('php://stderr', print_r('BuiltResponseClient: '.($endTime - $startTime).' ms ', true));

                return $builtResponse;
            } catch (\Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/satisfaction", name="admin_satisfaction")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/satisfaction.html.twig")
     */
    public function satisfaction(Request $request, ReportSatisfactionSummaryMapper $mapper): array|Response
    {
        $form = $this->createFilterTypeForm($request, SatisfactionFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = 'satisfaction.csv';

                $reportSatisfactionSummaries = $mapper->getBy($form->getData());
                $csv = $this->satisfactionCsvGenerator->generateSatisfactionResponsesCsv($reportSatisfactionSummaries);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/user-research", name="admin_user_research")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/urResponses.html.twig")
     */
    public function userResearchResponses(Request $request, UserResearchResponseSummaryMapper $mapper): array|Response
    {
        $form = $this->createFilterTypeForm($request, UserResearchResponseFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = 'user-research-responses.csv';

                $userResearchResponses = $mapper->getBy($form->getData());
                $reportData = json_decode($userResearchResponses, true)['data'];
                $csv = $this->userResearchResponseCsvGenerator->generateUserResearchResponsesCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function buildResponse($csvContent): Response
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $attachmentName = sprintf('cwsdigidepsopg00001%s.dat', date('YmdHi'));
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$attachmentName.'"');

        $response->sendHeaders();

        return $response;
    }

    /**
     * @Route("/metrics", name="admin_metrics")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Stats/metrics.html.twig")
     */
    public function metricsAction(Request $request): array|Response
    {
        $form = $this->createFilterTypeForm($request, StatPeriodType::class, false);

        $append = '';

        if ($form->isSubmitted() && $form->isValid()) {
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $append = "&startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
        }

        $metrics = ['satisfaction', 'reportsSubmitted', 'clients', 'registeredDeputies', 'respondents'];

        foreach ($metrics as $metric) {
            $all = $this->restClient->get('stats?metric='.$metric.$append, 'array');

            if ('respondents' != $metric) {
                $byRole = $this->restClient->get('stats?metric='.$metric.'&dimension[]=deputyType'.$append, 'array');
                $stats[$metric] = array_merge(
                    ['all' => $all[0]['amount']],
                    $this->mapToDeputyType($byRole)
                );
            } else {
                $stats[$metric] = ['all' => $all[0]['amount']];
            }
        }

        return [
            'stats' => $stats,
            'form' => $form->createView(),
        ];
    }

    /**
     * Map an array of metric responses to be addressible by deputyType.
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
     * @Route("/reports", name="admin_reports")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/reports.html.twig")
     */
    public function reports(): void
    {
    }

    /**
     * @Route("/reports/user_accounts", name="admin_user_account_reports")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/userAccountReports.html.twig")
     */
    public function userAccountReports(): array|Response
    {
        return $this->statsApi->getAdminUserAccountReportData();
    }

    /**
     * @Route("/reports/benefits-report-metrics", name="benefits_report_metrics")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/benefitsReportMetrics.html.twig")
     */
    public function benefitsReportMetrics(Request $request): array|Response
    {
        $form = $this->createFilterTypeForm($request, BenefitsMetricsFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $deputyType = $form->get('deputyType')->getData();
                $append = "?deputyType={$deputyType}";

                $startDate = $form->get('startDate')->getData();
                $endDate = $form->get('endDate')->getData();
                if (null !== $startDate && null !== $endDate) {
                    $append .= "&startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
                }

                $fileName = 'client-benefits-metrics.csv';
                $reportData = $this->statsApi->getBenefitsReportMetrics($append);
                $csv = $this->clientBenefitMetricsCsvGenerator->generateClientBenefitsMetricCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/downloadActiveLaysCsv", name="admin_active_lays_csv")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function downloadActiveLayCsv(): Response
    {
        $fileName = 'activeLays.csv';
        $reportData = $this->statsApi->getActiveLayReportData();
        $csv = $this->activeLaysCsvGenerator->generateActiveLaysCsv($reportData);

        return $this->csvResponseGeneration($fileName, $csv);
    }

    /**
     * @Route("/downloadAssetsTotalValues", name="admin_total_assets_values")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function downloadAssetsTotalValues(): Response
    {
        $fileName = 'totalAssets.csv';
        $reportData = $this->statsApi->getAssetsTotalValuesWithin12Months();
        $csv = $this->assetsTotalsCSVGenerator->generateAssetsTotalValuesCSV(json_decode($reportData, true));

        return $this->csvResponseGeneration($fileName, $csv);
    }

    /**
     * @Route("/reports/inactive-admin-users-report", name="inactive_admin_users_report")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/inactiveAdminUsersReport.html.twig")
     */
    public function inactiveAdminUsersReport(Request $request): array|Response
    {
        $form = $this->createForm(InactiveAdminReportFilterType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $inactivityPeriod = $form->get('inactivityPeriod')->getData();

                $append = '?inactivityPeriod='.$inactivityPeriod;

                $fileName = 'inactiveAdminUsers.csv';
                $reportData = $this->statsApi->getInactiveAdminUsers($append);
                $csv = $this->inactiveAdminUserCsvGenerator->generateInactiveAdminUsersCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (\Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/reports/imbalanceMetrics", name="report_imbalance_metrics")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Stats/imbalanceReportMetrics.html.twig")
     */
    public function reportImbalanceCsv(Request $request): array|Response
    {
        $form = $this->createFilterTypeForm($request, ImbalanceMetricsFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $append = '';

                $startDate = $form->get('startDate')->getData();
                $endDate = $form->get('endDate')->getData();
                if (null !== $startDate && null !== $endDate) {
                    $append .= "?startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
                }

                $fileName = 'reportImbalanceMetrics.csv';

                $reportData = $this->statsApi->getReportsImbalanceMetrics($append);
                $csv = $this->reportImbalanceCsvGenerator->generateReportImbalanceCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function csvResponseGeneration(string $fileName, string $csvContent): Response
    {
        $response = new Response($csvContent);

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function createFilterTypeForm(Request $request, string $fqcn, bool $dateRangeQuery = true)
    {
        $form = $dateRangeQuery ?
            $this->createForm($fqcn, new DateRangeQuery()) :
            $this->createForm($fqcn);

        return $form->handleRequest($request);
    }
}
