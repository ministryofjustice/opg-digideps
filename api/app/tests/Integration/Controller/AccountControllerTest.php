<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\AccountResponse;
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
        ['client' => $client, 'persons' => ['deputies' => ['lay' => $deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay', hasLogin: false))
        )));
        $decoded = $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname()),
            new NonEmptyString($deputy->getAddressPostcode()),
            new Email('test@test.test'),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountResponse::class, $response);
        $this->assertSame('test@test.test', $response->email->value);
    }

    public function testSet(): void
    {

    }
}
