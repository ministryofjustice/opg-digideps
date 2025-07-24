<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CourtOrder;
use App\Entity\User;
use App\Event\CoDeputyCreatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\InviteResult;
use App\Service\Client\RestClient;

class CourtOrderService
{
    public function __construct(
        private readonly RestClient $restClient,
        private readonly ObservableEventDispatcher $eventDispatcher,
    ) {
    }

    public function getByUid(string $uid): CourtOrder
    {
        return $this->restClient->getAndDeserialize(sprintf('v2/courtorder/%s', $uid), CourtOrder::class);
    }

    public function inviteLayDeputy(string $uid, User $invitedUser, User $invitingUser): InviteResult
    {
        // this can throw exceptions which are not marked on the class; in particular, a NoSuccess runtime exception
        /** @var InviteResult $result */
        $result = $this->restClient->post(
            sprintf('v2/courtorder/%s/lay-deputy-invite', $uid),
            [
                'email' => $invitedUser->getEmail(),
                'firstname' => $invitedUser->getFirstName(),
                'lastname' => $invitedUser->getLastName(),
            ]
        );

        // TODO check result and only send email if it was a success

        // get the invited deputy's user
        $invitedDeputyUser = new User();

        // this triggers the activation email to the new co-deputy
        $coDeputyCreatedEvent = new CoDeputyCreatedEvent($invitedDeputyUser, $invitingUser);
        $this->eventDispatcher->dispatch($coDeputyCreatedEvent, CoDeputyCreatedEvent::NAME);

        return new InviteResult(
            success: $result['success'],
            message: $result['message'],
            code: $result['code'],
            invitedUserId: $result['invitedUserId'],
        );
    }
}
