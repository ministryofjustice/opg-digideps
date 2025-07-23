<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\PreRegistrationRepository;
use App\Service\DeputyService;
use App\Service\UserService;
use App\v2\DTO\InviteeDTO;
use Psr\Log\LoggerInterface;
use Random\RandomException;

/**
 * Service for inviting deputies to an existing court order.
 *
 * Pre-requisites for successful invite:
 * - Court order must exist in court_order table
 * - Inviting deputy must have access to the court order
 *
 * Inputs (all are required to be non-null):
 * - UID of existing court order to invite deputy to
 * - email, firstname, lastname, and role_name (User::ROLE_* constant) of invited deputy
 * - Inviting deputy's User instance
 *
 * Outputs:
 * - dd_user, deputy, and court_order_deputy records are present for the invited deputy (created if they don't exist)
 * - dd_user.registration_token is recreated for user associated with the invited deputy
 *
 * Clarifications:
 * - did consider ignoring dd_user.role_name, but it's required for dd_user record (default = ROLE_LAY_DEPUTY)
 * - email code is all in the frontend, so this service does not send the notification email to the user
 * - if the new deputy already exists when we try to add them, we use the existing deputy; this means we won't be
 *   updating any of that deputy's details, just using the one already in the database
 */
class CourtOrderInviteService
{
    public function __construct(
        private readonly PreRegistrationRepository $preRegistrationRepository,
        private readonly CourtOrderService $courtOrderService,
        private readonly UserService $userService,
        private readonly DeputyService $deputyService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Invite the Lay deputy represented by $invitedDeputyData to court order with UID $courtOrderUid, acting as the
     * Lay deputy $invitingLayDeputy.
     *
     * This should not be used for PA/PRO invites as it requires a matching entry in the pre-reg table to make the
     * invite.
     */
    public function inviteLayDeputy(string $courtOrderUid, User $invitingLayDeputy, InviteeDTO $invitedDeputyDTO): bool
    {
        // check invited deputy is a Lay
        if (User::ROLE_LAY_DEPUTY !== $invitedDeputyDTO->roleName) {
            $this->logger->error("could not invite $invitedDeputyDTO->email as they are not a Lay deputy");

            return false;
        }

        // check the court order exists, and inviting deputy is a deputy on it
        $courtOrder = $this->courtOrderService->getByUidAsUser($courtOrderUid, $invitingLayDeputy);
        if (is_null($courtOrder)) {
            $this->logger->error(
                "could not invite $invitedDeputyDTO->email to court order $courtOrderUid: ".
                'either court order does not exist, or inviting deputy cannot access it'
            );

            return false;
        }

        // check invited deputy is in the prereg table and is associated with the court order's case number
        $caseNumber = $courtOrder->getClient()->getCaseNumber();
        $preRegRecord = $this->preRegistrationRepository->findInvitedLayDeputy($invitedDeputyDTO, $caseNumber);

        if (is_null($preRegRecord)) {
            $this->logger->error(
                "Deputy $invitedDeputyDTO->email with access to case $caseNumber not found in pre-reg table"
            );

            return false;
        }

        // check prereg record has deputy UID
        $deputyUid = $preRegRecord->getDeputyUid();
        if (empty($deputyUid) || !is_string($deputyUid)) {
            $this->logger->error("Deputy with email $invitedDeputyDTO->email has empty deputy UID in pre-reg table");

            return false;
        }

        // create user for invited deputy, or get existing user; if the latter, none of their data is updated
        try {
            $invitedUser = $this->userService->getOrAddUser($invitedDeputyDTO, $invitingLayDeputy);
        } catch (RandomException $e) {
            $this->logger->error("Unable to invite deputy as registration token could not be created: {$e->getMessage()}");

            return false;
        }

        // create deputy record if it doesn't exist, or get the existing deputy; if the latter, none of their data is updated
        $invitedDeputy = new Deputy();
        $invitedDeputy->setFirstname($invitedDeputyDTO->firstname);
        $invitedDeputy->setLastname($invitedDeputyDTO->lastname);
        $invitedDeputy->setEmail1($invitedDeputyDTO->email);
        $invitedDeputy->setDeputyUid($deputyUid);

        $deputy = $this->deputyService->getOrAddDeputy($invitedDeputy, $invitedUser);

        // associate deputy with court order, ignoring any existing duplicates
        $this->courtOrderService->associateDeputyWithCourtOrder($deputy, $courtOrder, logDuplicateError: false);

        return true;
    }
}
