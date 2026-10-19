<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client\Internal;

use OPG\Digideps\Frontend\Event\UserAddedToOrganisationEvent;
use OPG\Digideps\Frontend\Event\UserRemovedFromOrganisationEvent;
use OPG\Digideps\Frontend\EventDispatcher\ObservableEventDispatcher;
use OPG\Digideps\Frontend\Service\Client\Internal\OrganisationApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\TestHelpers\OrganisationHelpers;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrganisationApiTest extends TestCase
{
    private RestClient&MockObject $restClient;
    private ObservableEventDispatcher&MockObject $eventDispatcher;
    private OrganisationApi $sut;

    public function setUp(): void
    {
        $this->restClient = self::createMock(RestClient::class);
        $this->eventDispatcher = self::createMock(ObservableEventDispatcher::class);
        $this->sut = new OrganisationApi($this->restClient, $this->eventDispatcher);
    }

    public function testAddUserToOrganisation(): void
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToAdd = UserHelpers::createUser()->setOrganisations([$organisation]);
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $this->restClient->expects(self::once())
            ->method('put')
            ->with(sprintf('v2/organisation/%s/user/%s', $organisation->getId(), $userToAdd->getId()), '');

        $userAddedToOrgEvent = new UserAddedToOrganisationEvent(
            $organisation,
            $userToAdd,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with($userAddedToOrgEvent, 'user.added.to.organisation');

        $this->sut->addUserToOrganisation($organisation, $userToAdd, $currentUser, $trigger);
    }

    public function testRemoveUserFromOrganisation(): void
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToRemove = UserHelpers::createUser()->setOrganisations([$organisation]);
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $this->restClient->expects(self::once())
            ->method('delete')
            ->with(sprintf('v2/organisation/%s/user/%s', $organisation->getId(), $userToRemove->getId()));

        $userRemovedFromOrgEvent = new UserRemovedFromOrganisationEvent(
            $organisation,
            $userToRemove,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with($userRemovedFromOrgEvent, 'user.removed.from.organisation');

        $this->sut->removeUserFromOrganisation($organisation, $userToRemove, $currentUser, $trigger);
    }
}
