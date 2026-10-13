<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\NonEmptyString;

class AccountControllerTest extends AbstractTestController
{
    public function testReset(): void
    {

    }

    public function testRequest(): void
    {
        /**
         * @var Deputy $deputy
         */
        ['client' => $client, 'persons' => ['deputies' => [$deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay1', hasLogin: false))
        )));
        $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname()),
            new NonEmptyString($deputy->getAddressPostcode()),
            new Email('test@test.test'),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
    }

    public function testSet(): void
    {

    }
}
