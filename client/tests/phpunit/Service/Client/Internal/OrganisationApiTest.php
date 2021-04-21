<?php declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use App\Event\UserAddedToOrganisationEvent;
use App\Event\UserRemovedFromOrganisationEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\Internal\OrganisationApi;
use App\Service\Client\RestClient;
use App\TestHelpers\OrganisationHelper;
use App\TestHelpers\UserHelper;
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
        $organisation = OrganisationHelper::createActivatedOrganisation();
        $userToAdd = (UserHelper::createUser())->setOrganisations(new ArrayCollection([$organisation]));
        $currentUser = UserHelper::createUser();
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
            ->dispatch($userAddedToOrgEvent, 'user.added.to.organisation')
            ->shouldBeCalled();

        $this->sut->addUserToOrganisation($organisation, $userToAdd, $currentUser, $trigger);
    }

    /** @test */
    public function removeUserFromOrganisation()
    {
        $organisation = OrganisationHelper::createActivatedOrganisation();
        $userToRemove = (UserHelper::createUser())->setOrganisations(new ArrayCollection([$organisation]));
        $currentUser = UserHelper::createUser();
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
            ->dispatch($userRemovedFromOrgEvent, 'user.removed.from.organisation')
            ->shouldBeCalled();

        $this->sut->removeUserFromOrganisation($organisation, $userToRemove, $currentUser, $trigger);
    }
}
