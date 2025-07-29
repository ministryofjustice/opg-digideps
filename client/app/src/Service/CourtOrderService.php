<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CourtOrder;
use App\Entity\User;
use App\Event\CoDeputyCreatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\InviteResult;
use App\Service\Client\RestClient;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class CourtOrderService
{
    public function __construct(
        private readonly RestClient $restClient,
        private readonly ObservableEventDispatcher $eventDispatcher,
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

        /** @var ?array $body */
        $body = json_decode($stream->getContents(), associative: true);

        if (is_null($body) || !isset($body['success']) || !is_bool($body['success'])) {
            $message = 'Unable to send co-deputy invitation to user - malformed JSON or no `success` property set';
            $this->logger->error($message);

            return new InviteResult(
                success: false,
                message: $message,
            );
        }

        $success = $body['success'];

        if ($success) {
            // construct a dummy user object to use for email parameters (the User object passed to this method is not
            // persisted and doesn't have a registration token so we get the necessary data from the request+response)
            $newUser = new User();
            $newUser->setEmail($invitedUser->getEmail());
            $newUser->setRoleName(User::ROLE_LAY_DEPUTY);
            $newUser->setRegistrationToken($body['data']['registrationToken']);

            // trigger the event which sends the activation email to the new co-deputy
            $coDeputyCreatedEvent = new CoDeputyCreatedEvent($newUser, $invitingUser);
            $this->eventDispatcher->dispatch($coDeputyCreatedEvent, CoDeputyCreatedEvent::NAME);
        }

        return new InviteResult(
            success: $success,
            message: $body['data']['message'] ?? null,
        );
    }
}
