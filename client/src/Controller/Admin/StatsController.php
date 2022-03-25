<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\DisplayableException;
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
use App\Service\Csv\SatisfactionCsvGenerator;
use App\Service\Csv\UserResearchResponseCsvGenerator;
use App\Transformer\ReportSubmission\ReportSubmissionBurFixedWidthTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @Route("/admin/stats")
 */
class StatsController extends AbstractController
{
    public function __construct(
        private RestClient $restClient,
        private SatisfactionCsvGenerator $satisfactionCsvGenerator,
        private StatsApi $statsApi,
        private ActiveLaysCsvGenerator $activeLaysCsvGenerator,
        private UserResearchResponseCsvGenerator $userResearchResponseCsvGenerator,
        private AssetsTotalsCSVGenerator $assetsTotalsCSVGenerator,
    ) {
    }

    /**
     * @Route("", name="admin_stats")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Stats/stats.html.twig")
     *
     * @return array|Response
     */
    public function stats(Request $request, ReportSubmissionSummaryMapper $mapper, ReportSubmissionBurFixedWidthTransformer $transformer)
    {
        $form = $this->createForm(ReportSubmissionDownloadFilterType::class, new DateRangeQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSubmissionSummaries = $mapper->getBy($form->getData());
                $downloadableData = $transformer->transform($reportSubmissionSummaries);

                return $this->buildResponse($downloadableData);
            } catch (Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/satisfaction", name="admin_satisfaction")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Stats/satisfaction.html.twig")
     *
     * @return array|Response
     */
    public function satisfaction(Request $request, ReportSatisfactionSummaryMapper $mapper)
    {
        $form = $this->createForm(SatisfactionFilterType::class, new DateRangeQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reportSatisfactionSummaries = $mapper->getBy($form->getData());
                $csv = $this->satisfactionCsvGenerator->generateSatisfactionResponsesCsv($reportSatisfactionSummaries);

                $response = new Response($csv);

                $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'satisfaction.csv'
                );

                $response->headers->set('Content-Disposition', $disposition);

                return $response;
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
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Stats/urResponses.html.twig")
     *
     * @return array|Response
     */
    public function userResearchResponses(Request $request, UserResearchResponseSummaryMapper $mapper)
    {
        $form = $this->createForm(UserResearchResponseFilterType::class, new DateRangeQuery());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userResearchResponses = $mapper->getBy($form->getData());
                $userResearchResponsesArray = json_decode($userResearchResponses, true)['data'];
                $csv = $this->userResearchResponseCsvGenerator->generateUserResearchResponsesCsv($userResearchResponsesArray);

                $response = new Response($csv);

                $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                $disposition = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'user-research-responses.csv'
                );

                $response->headers->set('Content-Disposition', $disposition);

                return $response;
            } catch (Throwable $e) {
                throw new DisplayableException($e);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param $csvContent
     *
     * @return Response
     */
    private function buildResponse($csvContent)
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
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("@App/Admin/Stats/metrics.html.twig")
     *
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
            $all = $this->restClient->get('stats?metric='.$metric.$append, 'array');
            $byRole = $this->restClient->get('stats?metric='.$metric.'&dimension[]=deputyType'.$append, 'array');

            $stats[$metric] = array_merge(
                ['all' => $all[0]['amount']],
                $this->mapToDeputyType($byRole)
            );
        }

        return [
            'stats' => $stats,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/reports", name="admin_reports")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Stats/reports.html.twig")
     *
     * @return array|Response
     */
    public function reports()
    {
    }

    /**
     * @Route("/reports/user_accounts", name="admin_user_account_reports")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Stats/userAccountReports.html.twig")
     *
     * @return array|Response
     */
    public function userAccountReports()
    {
        return $this->statsApi->getAdminUserAccountReportData();
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

    /**
     * @Route("/downloadAssetsTotalValues", name="admin_total_assets_values")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @return Response
     */
    public function downloadAssetsTotalValues()
    {
        $activeLaysData = $this->statsApi->getAssetsTotalValuesWithin12Months();

        $csv = $this->assetsTotalsCSVGenerator->generateAssetsTotalValuesCSV(json_decode($activeLaysData, true));

        $response = new Response($csv);

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'totalAssets.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
