<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CourtOrder;
use App\Entity\User;
use App\Event\CoDeputyCreatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\InviteResult;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class CourtOrderService
{
    public function __construct(
        private readonly RestClient $restClient,
        private readonly ObservableEventDispatcher $eventDispatcher,
        private readonly UserApi $userApi,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getByUid(string $uid): CourtOrder
    {
        return $this->restClient->getAndDeserialize(sprintf('v2/courtorder/%s', $uid), CourtOrder::class);
    }

    public function inviteLayDeputy(string $uid, User $invitedUser, User $invitingUser): InviteResult
    {
        // using raw response stream here to avoid unwanted exceptions being thrown by the RestClient
        /** @var StreamInterface $stream */
        $stream = $this->restClient->post(
            sprintf('v2/courtorder/%s/lay-deputy-invite', $uid),
            [
                'email' => $invitedUser->getEmail(),
                'firstname' => $invitedUser->getFirstName(),
                'lastname' => $invitedUser->getLastName(),
            ],
            expectedResponseType: 'raw',
        );

        $body = json_decode($stream->getContents(), associative: true);

        $success = false;
        $message = $invitedUserId = null;
        if (!is_null($body) && isset($body['success']) && is_bool($body['success'])) {
            $success = $body['success'];
            $message = $body['data']['message'] ?? null;
            $invitedUserId = $body['data']['invitedUserId'] ?? null;
        }

        if (is_null($invitedUserId)) {
            $success = false;
            $message = 'Unable to find ID of user associated with invited deputy';
        }

        if ($success) {
            // fetch the user as saved to the db (the User object passed to this method is not persisted and
            // doesn't have a registration token)
            // TODO this doesn't work because the inviting deputy can't access the invited deputy's record
            // TODO instead, include contact details in the API response and serialise to <client>\User
            $persistedUser = $this->userApi->get($invitedUserId);

            // trigger the event which sends the activation email to the new co-deputy
            $coDeputyCreatedEvent = new CoDeputyCreatedEvent($persistedUser, $invitingUser);
            $this->eventDispatcher->dispatch($coDeputyCreatedEvent, CoDeputyCreatedEvent::NAME);
        } else {
            $this->logger->error("Unable to send co-deputy invitation to user - error was: $message");
        }

        return new InviteResult(
            success: $success,
            message: $message,
            invitedUserId: $invitedUserId,
        );
    }
}
