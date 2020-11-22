<?php declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Service\Client\Internal\OrganisationApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\OrganisationHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;

class OrganisationApiTest extends TestCase
{
    /** @test */
    public function addUser()
    {
        $userToAdd = UserHelpers::createUser();
        $organisation = OrganisationHelpers::createActivatedOrganisation();
        $restClient = self::prophesize(RestClient::class);
        $eventDispatcher = self::prophesize(ObservableEventDispatcher::class);

        $restClient
            ->put(sprintf('v2/organisation/%s/user/%s', $organisation->getId(), $userToAdd->getId()), '')
            ->shouldBeCalled();

        $sut = new OrganisationApi($restClient->reveal(), $eventDispatcher->reveal());
        $sut->addUserToOrganisation($organisation, $userToAdd);
    }
}
