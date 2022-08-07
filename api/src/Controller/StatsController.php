<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Ndr\AssetOther as NdrAssetOther;
use App\Entity\Ndr\AssetProperty as NdrAssetProperty;
use App\Entity\Report\AssetOther;
use App\Entity\Report\AssetProperty;
use App\Exception\UnauthorisedException;
use App\Repository\AssetRepository;
use App\Repository\BankAccountRepository;
use App\Repository\NdrAssetRepository;
use App\Repository\NdrBankAccountRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\Stats\QueryFactory;
use App\Service\Stats\StatsQueryParameters;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends RestController
{
    public function __construct(
        private QueryFactory $QueryFactory,
        private UserRepository $userRepository,
        private ReportRepository $reportRepository,
        private AssetRepository $assetRepository,
        private BankAccountRepository $bankAccountRepository,
        private NdrAssetRepository $ndrAssetRepository,
        private NdrBankAccountRepository $ndrBankAccountRepository,
        private AuthService $authService
    ) {
    }

    /**
     * @Route("/stats", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getMetric(Request $request)
    {
        $params = new StatsQueryParameters($request->query->all());
        $query = $this->QueryFactory->create($params);

        return $query->execute($params);
    }

    /**
     * @Route("stats/deputies/lay/active", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getActiveLays(Request $request)
    {
        if ($this->authService->JWTIsValid($request)) {
            return $this->userRepository->findActiveLaysInLastYear();
        }

        throw new UnauthorisedException('JWT is not valid');
    }

    /**
     * @Route("stats/admins/report_data", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getAdminUserAccountReportData(Request $request, RestFormatter $formatter): array
    {
        $serialisedGroups = (array) $request->query->get('groups');
        $formatter->setJmsSerialiserGroups($serialisedGroups);

        $adminAccounts = $this->userRepository->getAllAdminAccounts();
        $countOfAdminAccounts = count($adminAccounts);

        $inactiveAdminAccounts = $this->userRepository->getAllAdminAccountsCreatedButNotActivatedWithin('-60 days');
        $countOfInactiveAdminAccounts = count($inactiveAdminAccounts);

        $activatedAdminAccounts = $this->userRepository->getAllActivatedAdminAccounts();
        $countOfActivatedAdminAccounts = count($activatedAdminAccounts);

        $activatedAdminAccountsNotUsedWithin90Days = $this->userRepository->getAllAdminAccountsNotUsedWithin('-90 days');
        $countOfActivatedAdminAccountsNotUsedWithin90Days = count($activatedAdminAccountsNotUsedWithin90Days);

        $activatedAdminAccountsUsedWithin90Days = $this->userRepository->getAllAdminAccountsUsedWithin('-90 days');
        $countOfActivatedAdminAccountsUsedWithin90Days = count($activatedAdminAccountsUsedWithin90Days);

        return [
            'TotalAdminAccounts' => $countOfAdminAccounts,
            'InactiveAdminAccounts' => $countOfInactiveAdminAccounts,
            'ActivatedAdminAccounts' => $countOfActivatedAdminAccounts,
            'ActivatedAdminAccountsNotUsedWithin90Days' => $countOfActivatedAdminAccountsNotUsedWithin90Days,
            'ActivatedAdminAccountsUsedWithin90Days' => $countOfActivatedAdminAccountsUsedWithin90Days,
        ];
    }

    /**
     * @Route("stats/admins/old_report_data", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getOldAdminUserReportData(Request $request, Restformatter $formatter): array
    {
        $serialisedGroups = (array) $request->query->get('groups');
        $formatter->setJmsSerialiserGroups($serialisedGroups);

        $adminUserAccountsNotUsedWithin13Months = $this->userRepository->getAllAdminUserAccountsNotUsedWithin('-13 months');

        return [
            'AdminUserAccountsNotUsedWithin13Months' => $adminUserAccountsNotUsedWithin13Months,
        ];
    }

    /**
     * @Route("stats/assets/total_values", methods={"GET"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getAssetsTotalValueData()
    {
        $ret = [
            'lays' => ['liquid' => 0, 'non-liquid' => 0],
            'profs' => ['liquid' => 0, 'non-liquid' => 0],
            'pas' => ['liquid' => 0, 'non-liquid' => 0],
            'grandTotal' => 0,
        ];

        $oneYearAgo = new DateTime('-1 year');

        $ret['lays']['non-liquid'] += $this->assetRepository->getSumOfAssets(AssetOther::class, 'LAY', $oneYearAgo);
        $ret['profs']['non-liquid'] += $this->assetRepository->getSumOfAssets(AssetOther::class, 'PROF', $oneYearAgo);
        $ret['pas']['non-liquid'] += $this->assetRepository->getSumOfAssets(AssetOther::class, 'PA', $oneYearAgo);

        $ret['lays']['non-liquid'] += $this->assetRepository->getSumOfAssets(AssetProperty::class, 'LAY', $oneYearAgo);
        $ret['profs']['non-liquid'] += $this->assetRepository->getSumOfAssets(AssetProperty::class, 'PROF', $oneYearAgo);
        $ret['pas']['non-liquid'] += $this->assetRepository->getSumOfAssets(AssetProperty::class, 'PA', $oneYearAgo);

        $ret['lays']['liquid'] += $this->bankAccountRepository->getSumOfAccounts('LAY', $oneYearAgo);
        $ret['profs']['liquid'] += $this->bankAccountRepository->getSumOfAccounts('PROF', $oneYearAgo);
        $ret['pas']['liquid'] += $this->bankAccountRepository->getSumOfAccounts('PA', $oneYearAgo);

        $clientIdsOfSubmittedReports = $this->reportRepository->getClientIdsByAllSubmittedLayReportsWithin12Months();

        $ret['lays']['non-liquid'] += $this->ndrAssetRepository->getSumOfAssets(NdrAssetOther::class, $oneYearAgo, $clientIdsOfSubmittedReports);
        $ret['lays']['non-liquid'] += $this->ndrAssetRepository->getSumOfAssets(NdrAssetProperty::class, $oneYearAgo, $clientIdsOfSubmittedReports);
        $ret['lays']['liquid'] += $this->ndrBankAccountRepository->getSumOfAccounts($oneYearAgo, $clientIdsOfSubmittedReports);

        $ret['grandTotal'] =
            $ret['lays']['non-liquid'] +
            $ret['lays']['liquid'] +
            $ret['profs']['non-liquid'] +
            $ret['profs']['liquid'] +
            $ret['pas']['non-liquid'] +
            $ret['pas']['liquid'];

        return new JsonResponse($ret);
    }

    /**
     * @Route("stats/report/benefits-report-metrics", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function getBenefitsReportMetrics(Request $request): array
    {
        $deputyType = $request->query->get('deputyType');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        return $this->reportRepository->getBenefitsResponseMetrics($startDate, $endDate, $deputyType);
    }
}
