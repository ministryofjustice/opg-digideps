<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NdrRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\Service\Formatter\RestFormatter;
use App\Service\Stats\QueryFactory;
use App\Service\Stats\StatsQueryParameters;
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
        private NdrRepository $ndrRepository,
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
    public function getActiveLays()
    {
        return $this->userRepository->findActiveLaysInLastYear();
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

        $lays = $this->reportRepository->getAllSubmittedReportsWithin12Months('LAY');
        $profs = $this->reportRepository->getAllSubmittedReportsWithin12Months('PROF');
        $pas = $this->reportRepository->getAllSubmittedReportsWithin12Months('PA');
        $ndrs = $this->ndrRepository->getAllSubmittedNdrsWithin12Months();

        $layClientIds = [];

        foreach ($lays as $layReport) {
            $layClientIds[] = $layReport->getClientId();
            $ret['lays']['non-liquid'] += $layReport->getAssetsTotalValue();
            foreach ($layReport->getBankAccounts() as $bankAccount) {
                $ret['lays']['liquid'] += $bankAccount->getClosingBalance();
            }
        }

        foreach ($profs as $profReport) {
            $ret['profs']['non-liquid'] += $profReport->getAssetsTotalValue();
            foreach ($profReport->getBankAccounts() as $bankAccount) {
                $ret['profs']['liquid'] += $bankAccount->getClosingBalance();
            }
        }

        foreach ($pas as $paReport) {
            $ret['pas']['non-liquid'] += $paReport->getAssetsTotalValue();
            foreach ($paReport->getBankAccounts() as $bankAccount) {
                $ret['pas']['liquid'] += $bankAccount->getClosingBalance();
            }
        }

        foreach ($ndrs as $ndr) {
            if (in_array($ndr->getClient()->getId(), $layClientIds)) {
                continue;
            }

            $ret['lays']['non-liquid'] += $ndr->getAssetsTotalValue();
            foreach ($ndr->getBankAccounts() as $bankAccount) {
                $ret['lays']['liquid'] += $bankAccount->getClosingBalance();
            }
        }

        $ret['grandTotal'] =
            $ret['lays']['non-liquid'] +
            $ret['lays']['liquid'] +
            $ret['profs']['non-liquid'] +
            $ret['profs']['liquid'] +
            $ret['pas']['non-liquid'] +
            $ret['pas']['liquid'];

        return new JsonResponse($ret);
    }
}
