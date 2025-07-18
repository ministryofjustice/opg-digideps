<?php

declare(strict_types=1);

namespace App\v2\Service;

use App\Entity\Deputy;
use App\Service\DeputyService;
use App\v2\DTO\InviteeDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Service for inviting deputies to an existing court order.
 *
 * Pre-requisites for successful invite:
 * - Court order must exist in court_order table
 * - Inviting deputy must have access to the court order
 * - Invited deputy must exist in pre_registration table (based on SELECT by email supplied by inviting deputy)
 *
 * Inputs (all are required to be non-null):
 * - UID of existing court order to invite deputy to
 * - email, firstname, lastname, and role_name (User::ROLE_* constant) of invited deputy
 * - Inviting deputy's User instance
 *
 * Outputs:
 * - dd_user, deputy, and court_order_deputy records are present for the invited deputy (created if they don't exist)
 * - dd_user.registration_token is recreated for user associated with the invited deputy
 * - Invited deputy is sent an email with an activation link (using new registration_token)
 *
 * Clarifications:
 * - did consider ignoring dd_user.role_name, but it's required for dd_user record (default = ROLE_LAY_DEPUTY)
 */
class CourtOrderInviteService
{
    public function __construct(
        private readonly CourtOrderService $courtOrderService,
        private readonly DeputyService $deputyService,
        private readonly UserService $userService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Invite the $invitedDeputy to court order with UID $courtOrderUid, acting as the $invitingDeputy.
     */
    public function invite(string $courtOrderUid, ?UserInterface $invitingDeputy, InviteeDTO $invitedDeputy): bool
    {
        // does the court order exist, and is inviting deputy a deputy on it?
        $courtOrder = $this->courtOrderService->getByUidAsUser($courtOrderUid, $invitingDeputy);

        if (is_null($courtOrder)) {
            $this->logger->error(
                "could not invite {$invitedDeputy->email} to court order $courtOrderUid: ".
                'either court order does not exist, or inviting deputy cannot access it'
            );

            return false;
        }

        // create user if they don't exist

        // recreate registration token on dd_user

        // create deputy record if it doesn't exist

        // associate deputy with court order, ignoring any existing duplicates

        return true;
    }
}
