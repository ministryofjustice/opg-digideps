<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\Deputy;
use App\Entity\User;
use App\Repository\PreRegistrationRepository;
use App\Service\DeputyService;
use App\Service\UserService;
use App\v2\DTO\InvitedDto;
use App\v2\DTO\InviteeDto;
use Doctrine\ORM\EntityManagerInterface;

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
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Invite the Lay deputy represented by $invitedDeputyData to court order with UID $courtOrderUid, acting as the
     * Lay deputy $invitingLayDeputy.
     *
     * This should not be used for PA/PRO invites as it requires a matching entry in the pre-reg table to make the
     * invite.
     */
    public function inviteLayDeputy(string $courtOrderUid, User $invitingLayDeputy, InviteeDto $invitedDeputyDTO): InvitedDto
    {
        $invitationResult = new InvitedDto();
        $errorPrefix = "Could not invite $invitedDeputyDTO->email to court order $courtOrderUid: ";

        // check invited deputy is a Lay
        if (User::ROLE_LAY_DEPUTY !== $invitedDeputyDTO->roleName) {
            return $invitationResult->setMessage("$errorPrefix invited deputy is not a Lay deputy");
        }

        // check the court order exists, and inviting deputy is a deputy on it
        $courtOrder = $this->courtOrderService->getByUidAsUser($courtOrderUid, $invitingLayDeputy);
        if (is_null($courtOrder)) {
            return $invitationResult->setMessage(
                "$errorPrefix either court order does not exist, or inviting deputy cannot access it"
            );
        }

        // check invited deputy is in the prereg table and is associated with the court order's case number
        $caseNumber = $courtOrder->getClient()->getCaseNumber();
        if (is_null($caseNumber)) {
            return $invitationResult->setMessage("$errorPrefix could not find case number for court order");
        }

        $preRegRecord = $this->preRegistrationRepository->findInvitedLayDeputy($invitedDeputyDTO, $caseNumber);

        if (is_null($preRegRecord)) {
            return $invitationResult->setMessage("$errorPrefix no record in pre-reg table");
        }

        // check prereg record has a deputy UID set
        $deputyUid = $preRegRecord->getDeputyUid();
        if (empty($deputyUid) || !is_string($deputyUid)) {
            return $invitationResult->setMessage("$errorPrefix empty deputy UID in pre-reg table");
        }

        // candidate deputy: will only be created if a deputy with this UID does not exist
        $invitedLayDeputy = new Deputy();
        $invitedLayDeputy->setFirstname($invitedDeputyDTO->firstname);
        $invitedLayDeputy->setLastname($invitedDeputyDTO->lastname);
        $invitedLayDeputy->setEmail1($invitedDeputyDTO->email);
        $invitedLayDeputy->setDeputyUid($deputyUid);

        // save stuff to db inside a transaction
        $this->entityManager->beginTransaction();

        try {
            // create user for invited deputy, or get existing user; if the latter, none of their data is updated
            // (unless their registration token is null, in which case it is recreated)
            $persistedUser = $this->userService->getOrAddUser($invitedDeputyDTO, $invitingLayDeputy);

            // create deputy record if it doesn't exist, or get the existing deputy; if the latter, none of their data is updated
            $persistedDeputy = $this->deputyService->getOrAddDeputy($invitedLayDeputy, $persistedUser);

            // associate deputy with court order, ignoring any existing duplicates
            $this->courtOrderService->associateDeputyWithCourtOrder($persistedDeputy, $courtOrder, logDuplicateError: false);
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return $invitationResult->setMessage("$errorPrefix unexpected error inserting data: {$e->getMessage()}");
        }

        $this->entityManager->commit();

        $invitationResult->success = true;
        $invitationResult->registrationToken = $persistedUser->getRegistrationToken();
        $invitationResult->message = 'Invitation sent successfully';

        return $invitationResult;
    }
}
