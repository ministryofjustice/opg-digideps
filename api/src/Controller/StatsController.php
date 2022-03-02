<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Formatter\RestFormatter;
use App\Service\Stats\QueryFactory;
use App\Service\Stats\StatsQueryParameters;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends RestController
{
    private QueryFactory $QueryFactory;
    private UserRepository $userRepository;

    public function __construct(
        QueryFactory $QueryFactory,
        UserRepository $userRepository
    ) {
        $this->QueryFactory = $QueryFactory;
        $this->userRepository = $userRepository;
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
}
