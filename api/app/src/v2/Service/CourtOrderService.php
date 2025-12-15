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


    private function transformReportDates(array $reportArray): array
    {
        $dateFields = [
            'submit_date' => 'Y-m-d\TH:i:sP',
            'un_submit_date' => 'Y-m-d',
            'start_date' => 'Y-m-d',
            'end_date' => 'Y-m-d',
            'due_date' => 'Y-m-d',
        ];

        foreach ($dateFields as $field => $format) {
            $reportArray[$field] = !empty($reportArray[$field])
                ? (new \DateTimeImmutable($reportArray[$field]))->format($format)
                : null;
        }

        return $reportArray;
    }

    public function getCourtOrderData(string $uid, ?User $user): ?array
    {
        if (is_null($user)) {
            return null;
        }
        $userId = $user->getId();

        /** @var array<int, array<string, mixed>> $courtOrderData */
        $courtOrderData = $this->courtOrderRepository->findCourtOrderByUid($uid) ?? [];

        if ($courtOrderData === []) {
            return null;
        }

        // ===== Deputies + Authorisation Check =====
        $deputiesSqlResults = $this->deputyRepository->findDeputiesByCourtOrderUID($uid);

        $authorisedToViewCourtOrder = false;

        $courtOrderData['active_deputies'] = [];
        foreach ($deputiesSqlResults as $deputy) {
            // Must have a numeric user_id to proceed
            if (is_null($deputy['user_id'])) {
                continue;
            }
            $deputyUserId = $deputy['user_id'];

            // Authorisation flag
            if ($deputyUserId === $userId) {
                $authorisedToViewCourtOrder = true;
            }

            $userArray = $this->entityManager
                ->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.id = :id')
                ->setParameter('id', $deputyUserId)
                ->getQuery()
                ->getArrayResult();

            unset($deputy['user_id']);
            $deputy['user'] = $userArray[0] ?? null;
            if (!is_null($deputy)) {
                $courtOrderData['active_deputies'][] = $deputy;
            }
        }

        if (!$authorisedToViewCourtOrder) {
            return null;
        }

        // ===== Client =====
        /** @var array<int, array<string, mixed>> $clientSqlResults */
        $clientSqlResults = $this->clientRepository->findClientByCourtOrderUid($uid) ?? null;
        $courtOrderData['client'] = $clientSqlResults;

        // ===== Reports =====
        $reportsSqlResults = $this->reportRepository->findReportsByCourtOrderUid($uid);
        $courtOrderData['reports'] = [];

        foreach ($reportsSqlResults as $report) {
            $report['status'] = (array) ($report['status'] ?? []);
            $report['status']['status'] = $report['report_status_cached'] ?? null;
            $report = $this->transformReportDates($report);
            $report['submitted_by'] = null; // not used so to avoid extra API calls we set to null

            $courtOrderData['reports'][] = $report;
        }

        return $courtOrderData;
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
