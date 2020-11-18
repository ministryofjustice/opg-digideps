<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OrganisationApi
{
    private const ADD_USER_TO_ORG_ENDPOINT = 'v2/organisation/%s/user/%s';

    /** @var RestClient */
    private $restClient;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(RestClient $restClient, EventDispatcherInterface $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addUserToOrganisation(Organisation $organisation, User $userToAdd)
    {
        $this->restClient->put(sprintf(self::ADD_USER_TO_ORG_ENDPOINT, $organisation->getId(), $userToAdd->getId()), '');
    }
}
