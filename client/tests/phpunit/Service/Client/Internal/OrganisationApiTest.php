<?php declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Event\UserAddedToOrganisationEvent;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\Internal\OrganisationApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\OrganisationHelpers;
use AppBundle\TestHelpers\UserHelpers;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class OrganisationApiTest extends TestCase
{
    /** @test */
    public function addUser()
    {
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $userToAdd = (UserHelpers::createUser())->setOrganisations(new ArrayCollection([$organisation]));
        $currentUser = UserHelpers::createUser();
        $trigger = 'A_TRIGGER';

        $restClient = self::prophesize(RestClient::class);
        $eventDispatcher = self::prophesize(ObservableEventDispatcher::class);

        $restClient
            ->put(sprintf('v2/organisation/%s/user/%s', $organisation->getId(), $userToAdd->getId()), '')
            ->shouldBeCalled();

        $userAddedToOrgEvent = new UserAddedToOrganisationEvent(
            $organisation,
            $userToAdd,
            $currentUser,
            $trigger
        );

        $eventDispatcher
            ->dispatch('user.added.to.organisation', $userAddedToOrgEvent)
            ->shouldBeCalled();

        $sut = new OrganisationApi($restClient->reveal(), $eventDispatcher->reveal());
        $sut->addUserToOrganisation($organisation, $userToAdd, $currentUser, $trigger);
    }
}
