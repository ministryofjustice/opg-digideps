<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Controller\Admin;

use OPG\Digideps\Frontend\Controller\AbstractController;
use OPG\Digideps\Frontend\Exception\DisplayableException;
use OPG\Digideps\Frontend\Form\Admin\BenefitsMetricsFilterType;
use OPG\Digideps\Frontend\Form\Admin\ImbalanceMetricsFilterType;
use OPG\Digideps\Frontend\Form\Admin\InactiveAdminReportFilterType;
use OPG\Digideps\Frontend\Form\Admin\ReportSubmissionDownloadFilterType;
use OPG\Digideps\Frontend\Form\Admin\SatisfactionFilterType;
use OPG\Digideps\Frontend\Form\Admin\StatPeriodType;
use OPG\Digideps\Frontend\Form\Admin\UserResearchResponseFilterType;
use OPG\Digideps\Frontend\Mapper\DateRangeQuery;
use OPG\Digideps\Frontend\Mapper\ReportSatisfaction\ReportSatisfactionSummaryMapper;
use OPG\Digideps\Frontend\Mapper\ReportSubmission\ReportSubmissionSummaryMapper;
use OPG\Digideps\Frontend\Mapper\UserResearchResponse\UserResearchResponseSummaryMapper;
use OPG\Digideps\Frontend\Service\Client\Internal\StatsApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\Csv\ActiveLaysCsvGenerator;
use OPG\Digideps\Frontend\Service\Csv\AssetsTotalsCSVGenerator;
use OPG\Digideps\Frontend\Service\Csv\ClientBenefitMetricsCsvGenerator;
use OPG\Digideps\Frontend\Service\Csv\InactiveAdminUsersCsvGenerator;
use OPG\Digideps\Frontend\Service\Csv\ReportImbalanceCsvGenerator;
use OPG\Digideps\Frontend\Service\Csv\SatisfactionCsvGenerator;
use OPG\Digideps\Frontend\Service\Csv\UserResearchResponseCsvGenerator;
use OPG\Digideps\Frontend\Transformer\ReportSubmission\ReportSubmissionBurFixedWidthTransformer;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/stats')]
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

    #[Route(path: '', name: 'admin_stats')]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    #[Template('@App/Admin/Stats/stats.html.twig')]
    public function stats(
        Request $request,
        ReportSubmissionSummaryMapper $mapper,
        ReportSubmissionBurFixedWidthTransformer $transformer
    ): array|Response {
        $form = $this->createFilterTypeForm($request, ReportSubmissionDownloadFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var DateRangeQuery $query */
                $query = $form->getData();
                $reportSubmissionSummaries = $mapper->getBy($query);

                /** @var string $downloadableData */
                $downloadableData = $transformer->transform($reportSubmissionSummaries);

                return $this->buildResponse($downloadableData);
            } catch (\Throwable $e) {
                throw new DisplayableException($e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/satisfaction', name: 'admin_satisfaction')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/satisfaction.html.twig')]
    public function satisfaction(Request $request, ReportSatisfactionSummaryMapper $mapper): array|Response
    {
        $form = $this->createFilterTypeForm($request, SatisfactionFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = 'satisfaction.csv';

                /** @var DateRangeQuery $query */
                $query = $form->getData();
                $reportSatisfactionSummaries = $mapper->getBy($query);
                $csv = $this->satisfactionCsvGenerator->generateSatisfactionResponsesCsv($reportSatisfactionSummaries);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (\Throwable $e) {
                throw new DisplayableException($e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/user-research', name: 'admin_user_research')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/urResponses.html.twig')]
    public function userResearchResponses(Request $request, UserResearchResponseSummaryMapper $mapper): array|Response
    {
        $form = $this->createFilterTypeForm($request, UserResearchResponseFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = 'user-research-responses.csv';

                /** @var DateRangeQuery $query */
                $query = $form->getData();

                $userResearchResponses = $mapper->getBy($query);

                $reportData = json_decode($userResearchResponses->getContents(), true)['data'];
                $csv = $this->userResearchResponseCsvGenerator->generateUserResearchResponsesCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (\Throwable $e) {
                throw new DisplayableException($e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function buildResponse(string $csvContent): Response
    {
        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'application/octet-stream');

        $attachmentName = sprintf('cwsdigidepsopg00001%s.dat', date('YmdHi'));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $attachmentName . '"');

        $response->sendHeaders();

        return $response;
    }

    #[Route(path: '/metrics', name: 'admin_metrics')]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    #[Template('@App/Admin/Stats/metrics.html.twig')]
    public function metricsAction(Request $request): array|Response
    {
        $form = $this->createFilterTypeForm($request, StatPeriodType::class, false);

        $append = '';

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \DateTime $startDate */
            $startDate = $form->get('startDate')->getData();

            /** @var \DateTime $endDate */
            $endDate = $form->get('endDate')->getData();

            $append = "&startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
        }

        $metrics = ['satisfaction', 'reportsSubmitted', 'clients', 'registeredDeputies', 'respondents'];

        foreach ($metrics as $metric) {
            $all = $this->restClient->get('stats?metric=' . $metric . $append, 'array');

            if ('respondents' != $metric) {
                $byRole = $this->restClient->get('stats?metric=' . $metric . '&dimension[]=deputyType' . $append, 'array');
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
     * Map an array of metric responses to be addressable by deputyType.
     */
    private function mapToDeputyType(array $result): array
    {
        $resultByDeputyType = [];

        foreach ($result as $resultBit) {
            $resultByDeputyType[$resultBit['deputyType']] = $resultBit['amount'];
        }

        return $resultByDeputyType;
    }

    #[Route(path: '/reports', name: 'admin_reports')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/reports.html.twig')]
    public function reports(): void
    {
    }

    #[Route(path: '/reports/user_accounts', name: 'admin_user_account_reports')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/userAccountReports.html.twig')]
    public function userAccountReports(): array|Response
    {
        return $this->statsApi->getAdminUserAccountReportData();
    }

    #[Route(path: '/reports/benefits-report-metrics', name: 'benefits_report_metrics')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/benefitsReportMetrics.html.twig')]
    public function benefitsReportMetrics(Request $request): array|Response
    {
        $form = $this->createFilterTypeForm($request, BenefitsMetricsFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var string $deputyType */
                $deputyType = $form->get('deputyType')->getData();
                $append = "?deputyType=$deputyType";

                /** @var ?\DateTime $startDate */
                $startDate = $form->get('startDate')->getData();

                /** @var ?\DateTime $endDate */
                $endDate = $form->get('endDate')->getData();

                if (null !== $startDate && null !== $endDate) {
                    $append .= "&startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
                }

                $fileName = 'client-benefits-metrics.csv';
                $reportData = $this->statsApi->getBenefitsReportMetrics($append);
                $csv = $this->clientBenefitMetricsCsvGenerator->generateClientBenefitsMetricCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (\Throwable $e) {
                throw new DisplayableException($e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/downloadActiveLaysCsv', name: 'admin_active_lays_csv')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function downloadActiveLayCsv(): Response
    {
        $fileName = 'activeLays.csv';
        $reportData = $this->statsApi->getActiveLayReportData();
        $csv = $this->activeLaysCsvGenerator->generateActiveLaysCsv($reportData);

        return $this->csvResponseGeneration($fileName, $csv);
    }

    #[Route(path: '/downloadAssetsTotalValues', name: 'admin_total_assets_values')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function downloadAssetsTotalValues(): Response
    {
        $fileName = 'totalAssets.csv';
        $reportData = $this->statsApi->getAssetsTotalValuesWithin12Months();
        $csv = $this->assetsTotalsCSVGenerator->generateAssetsTotalValuesCSV(json_decode($reportData, true));

        return $this->csvResponseGeneration($fileName, $csv);
    }

    #[Route(path: '/reports/inactive-admin-users-report', name: 'inactive_admin_users_report')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/inactiveAdminUsersReport.html.twig')]
    public function inactiveAdminUsersReport(Request $request): array|Response
    {
        $form = $this->createForm(InactiveAdminReportFilterType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $inactivityPeriod = $form->get('inactivityPeriod')->getData();

                $append = '?inactivityPeriod=' . $inactivityPeriod;

                $fileName = 'inactiveAdminUsers.csv';
                $reportData = $this->statsApi->getInactiveAdminUsers($append);
                $csv = $this->inactiveAdminUserCsvGenerator->generateInactiveAdminUsersCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (\Throwable $e) {
                throw new DisplayableException($e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/reports/imbalanceMetrics', name: 'report_imbalance_metrics')]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    #[Template('@App/Admin/Stats/imbalanceReportMetrics.html.twig')]
    public function reportImbalanceCsv(Request $request): array|Response
    {
        $form = $this->createFilterTypeForm($request, ImbalanceMetricsFilterType::class);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $append = '';

                /** @var ?\DateTime $startDate */
                $startDate = $form->get('startDate')->getData();

                /** @var ?\DateTime $endDate */
                $endDate = $form->get('endDate')->getData();

                if (null !== $startDate && null !== $endDate) {
                    $append .= "?startDate={$startDate->format('Y-m-d')}&endDate={$endDate->format('Y-m-d')}";
                }

                $fileName = 'reportImbalanceMetrics.csv';

                $reportData = $this->statsApi->getReportsImbalanceMetrics($append);
                $csv = $this->reportImbalanceCsvGenerator->generateReportImbalanceCsv($reportData);

                return $this->csvResponseGeneration($fileName, $csv);
            } catch (\Throwable $e) {
                throw new DisplayableException($e->getMessage());
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

    private function createFilterTypeForm(Request $request, string $fqcn, bool $dateRangeQuery = true): FormInterface
    {
        $form = $dateRangeQuery ?
            $this->createForm($fqcn, new DateRangeQuery()) :
            $this->createForm($fqcn);

        return $form->handleRequest($request);
    }
}
