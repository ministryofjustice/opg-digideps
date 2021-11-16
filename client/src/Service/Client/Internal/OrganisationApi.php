<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Organisation;
use App\Entity\User;
use App\Event\UserAddedToOrganisationEvent;
use App\Event\UserRemovedFromOrganisationEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\RestClient;

class OrganisationApi
{
    private const MANAGE_USER_IN_ORG_ENDPOINT = 'v2/organisation/%s/user/%s';

    public function __construct(private RestClient $restClient, private ObservableEventDispatcher $eventDispatcher)
    {
    }

    public function addUserToOrganisation(Organisation $organisation, User $userToAdd, User $currentUser, string $trigger)
    {
        $this->restClient->put(sprintf(self::MANAGE_USER_IN_ORG_ENDPOINT, $organisation->getId(), $userToAdd->getId()), '');

        $event = new UserAddedToOrganisationEvent(
            $organisation,
            $userToAdd,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher->dispatch($event, UserAddedToOrganisationEvent::NAME);
    }

    public function removeUserFromOrganisation(Organisation $organisation, User $userToRemove, User $currentUser, string $trigger)
    {
        $this->restClient->delete(sprintf(self::MANAGE_USER_IN_ORG_ENDPOINT, $organisation->getId(), $userToRemove->getId()));

        $event = new UserRemovedFromOrganisationEvent(
            $organisation,
            $userToRemove,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher->dispatch($event, UserRemovedFromOrganisationEvent::NAME);
    }
}
