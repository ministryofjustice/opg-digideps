<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\CourtOrderRepository;
use App\Repository\UserRepository;
use App\Repository\ReportRepository;
use App\Repository\ClientRepository;
use App\Repository\DeputyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CourtOrderService
{
    public function __construct(
        private readonly CourtOrderRepository $courtOrderRepository,
        private readonly UserRepository $userRepository,
        private readonly ReportRepository $reportRepository,
        private readonly ClientRepository $clientRepository,
        private readonly DeputyRepository $deputyRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    private function transformDate(?string $dateValue, string $fieldName, string $format): ?string
    {
        if (empty($dateValue)) {
            return null;
        }

        try {
            $date = new \DateTimeImmutable($dateValue);
            return $date->format($format);
        } catch (\Exception $e) {
            // Optional: log the error for debugging
            error_log(sprintf(
                "Date transform failed for %s: %s (value: %s)\n",
                $fieldName,
                $e->getMessage(),
                $dateValue
            ));
            return null;
        }
    }

    public function transformReportDates(array $reportArray): ?array
    {
        $reportArray['due_date'] = $this->transformDate($reportArray['due_date'], 'dueDate', 'Y-m-d');
        $reportArray['submit_date'] = $this->transformDate($reportArray['submit_date'], 'submitDate', 'Y-m-d\TH:i:sP');
        $reportArray['un_submit_date'] = $this->transformDate($reportArray['un_submit_date'], 'unSubmitDate', 'Y-m-d');
        $reportArray['start_date'] = $this->transformDate($reportArray['start_date'], 'startDate', 'Y-m-d');
        $reportArray['end_date'] = $this->transformDate($reportArray['end_date'], 'endDate', 'Y-m-d');

        return $reportArray;
    }

    public function getCourtOrderView(string $uid, ?UserInterface $user): ?array
    {
        if ($user === null || !method_exists($user, 'getId')) {
            return null;
        }
        $userId = (int) $user->getId();

        /** @var array<int, array<string, mixed>> $courtOrderView */
        $courtOrderView = $this->courtOrderRepository->findCourtOrderByUID($uid) ?? [];
        if ($courtOrderView === []) {
            return null;
        }

        // ===== Deputies + Authorisation Check =====
        /** @var array<int, array<string, mixed>> $deputiesSqlResults */
        $deputiesSqlResults = $this->deputyRepository->findDeputiesByUID($uid) ?? [];

        $authorisedToViewCourtOrder = false;

        $courtOrderView['active_deputies'] = [];
        foreach ($deputiesSqlResults as $deputy) {
            if (!is_array($deputy)) {
                continue;
            }
            // Must have a numeric user_id to proceed
            if (!isset($deputy['user_id']) || !is_numeric($deputy['user_id'])) {
                continue;
            }
            $deputyUserId = (int) $deputy['user_id'];

            // Authorisation flag
            if ($deputyUserId === $userId) {
                $authorisedToViewCourtOrder = true;
            }

            // Fetch deputy user details
            /** @var array<int, array<string, mixed>> $userSqlResults */
            $userSqlResults = $this->userRepository->findUserById($deputyUserId) ?? null;
            unset($deputy['user_id']);
            $deputy['user'] = $userSqlResults;
            $courtOrderView['active_deputies'][] = $deputy;
        }

        if (!$authorisedToViewCourtOrder) {
            return null;
        }

        // ===== Client =====
        /** @var array<int, array<string, mixed>> $clientSqlResults */
        $clientSqlResults = $this->clientRepository->findClientByCourtOrderUID($uid) ?? null;
        $courtOrderView['client'] = $clientSqlResults;

        // ===== Reports =====
        /** @var array<int, array<string, mixed>> $reportsSqlResults */
        $reportsSqlResults = $this->reportRepository->findReportsByCourtOrderUID($uid) ?? [];
        $courtOrderView['reports'] = [];

        foreach ($reportsSqlResults as $report) {
            if (!is_array($report)) {
                continue;
            }
            assert(is_array($report));

            $report['status'] = (array) ($report['status'] ?? []);
            $report['status']['status'] = $report['report_status_cached'] ?? null;
            $report = $this->transformReportDates($report);
            $report['submitted_by'] = null; // not used so to avoid extra API calls we set to null

            $courtOrderView['reports'][] = $report;
        }

        return $courtOrderView;
    }

    /**
     * Get a court order by UID $uid, but only if $user is a deputy on it.
     */
    public function getByUidAsUser(string $uid, ?UserInterface $user): ?CourtOrder
    {
        if (is_null($user)) {
            return null;
        }

        /** @var ?CourtOrder $courtOrder */
        $courtOrder = $this->courtOrderRepository->findOneBy(['courtOrderUid' => $uid]);

        if (is_null($courtOrder)) {
            $this->logger->error("Could not find court order with UID {$uid}");

            return null;
        }

        // fetch the deputy entity by user email
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()]);

        /** @var ?Deputy $deputy */
        $deputy = $user?->getDeputy();

        if (is_null($deputy)) {
            $this->logger->error("Access denied to court order {$uid} as deputy was not found for logged-in user");

            return null;
        }

        $deputyUid = $deputy->getDeputyUid();

        // only return court order if the logged-in user is a deputy on it
        $isDeputyOnCourtOrder = false;
        /** @var Deputy $activeDeputy */
        foreach ($courtOrder->getActiveDeputies() as $activeDeputy) {
            if ($activeDeputy->getDeputyUid() === $deputyUid) {
                $isDeputyOnCourtOrder = true;
                break;
            }
        }

        if (!$isDeputyOnCourtOrder) {
            $this->logger->error(
                "Access denied to court order {$uid} for deputy with UID {$deputyUid} as they are not a deputy on order"
            );

            return null;
        }

        return $courtOrder;
    }

    /**
     * Associate deputy entity with court order entity. Entities are persisted.
     *
     * If there is already an association, this updates the status of the existing association.
     */
    public function associateCourtOrderWithDeputy(Deputy $deputy, CourtOrder $courtOrder, bool $isActive = true): void
    {
        $deputy->associateWithCourtOrder($courtOrder, $isActive);
        $this->entityManager->persist($deputy);
        $this->entityManager->flush();
    }
}
