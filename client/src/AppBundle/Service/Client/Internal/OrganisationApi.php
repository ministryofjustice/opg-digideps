<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Event\UserAddedToOrganisationEvent;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\RestClient;

class OrganisationApi
{
    private const ADD_USER_TO_ORG_ENDPOINT = 'v2/organisation/%s/user/%s';

    private RestClient $restClient;
    private ObservableEventDispatcher $eventDispatcher;

    public function __construct(RestClient $restClient, ObservableEventDispatcher $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addUserToOrganisation(Organisation $organisation, User $userToAdd, User $currentUser, string $trigger)
    {
        $this->restClient->put(sprintf(self::ADD_USER_TO_ORG_ENDPOINT, $organisation->getId(), $userToAdd->getId()), '');

        $event = new UserAddedToOrganisationEvent(
            $organisation,
            $userToAdd,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher->dispatch(UserAddedToOrganisationEvent::NAME, $event);
    }
}
