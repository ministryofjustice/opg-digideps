<?php declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Event\UserAddedToOrganisationEvent;
use AppBundle\Event\UserRemovedFromOrganisationEvent;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\Internal\OrganisationApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\OrganisationHelpers;
use AppBundle\TestHelpers\UserHelpers;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class OrganisationApiTest extends TestCase
{
    private ObjectProphecy $restClient;
    private ObjectProphecy $eventDispatcher;
    private OrganisationApi $sut;

    public function setUp(): void
    {
        $this->restClient = self::prophesize(RestClient::class);
        $this->eventDispatcher = self::prophesize(ObservableEventDispatcher::class);
        $this->sut = new OrganisationApi($this->restClient->reveal(), $this->eventDispatcher->reveal());
    }

    /** @test */
    public function addUserToOrganisation()
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToAdd = (UserHelpers::createUser())->setOrganisations(new ArrayCollection([$organisation]));
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $this->restClient
            ->put(sprintf('v2/organisation/%s/user/%s', $organisation->getId(), $userToAdd->getId()), '')
            ->shouldBeCalled();

        $userAddedToOrgEvent = new UserAddedToOrganisationEvent(
            $organisation,
            $userToAdd,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher
            ->dispatch('user.added.to.organisation', $userAddedToOrgEvent)
            ->shouldBeCalled();

        $this->sut->addUserToOrganisation($organisation, $userToAdd, $currentUser, $trigger);
    }

    /** @test */
    public function removeUserFromOrganisation()
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToRemove = (UserHelpers::createUser())->setOrganisations(new ArrayCollection([$organisation]));
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $this->restClient
            ->delete(sprintf('v2/organisation/%s/user/%s', $organisation->getId(), $userToRemove->getId()))
            ->shouldBeCalled();

        $userRemovedFromOrgEvent = new UserRemovedFromOrganisationEvent(
            $organisation,
            $userToRemove,
            $currentUser,
            $trigger
        );

        $this->eventDispatcher
            ->dispatch('user.removed.from.organisation', $userRemovedFromOrgEvent)
            ->shouldBeCalled();

        $this->sut->removeUserFromOrganisation($organisation, $userToRemove, $currentUser, $trigger);
    }
}
