<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Controller;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Account\AccountError;
use OPG\Digideps\Common\Account\AccountRequest;
use OPG\Digideps\Common\Account\AccountResponse;
use OPG\Digideps\Common\Account\Email;
use OPG\Digideps\Common\Account\NonEmptyString;
use OPG\Digideps\Common\Account\Password;
use OPG\Digideps\Common\Account\PasswordRequest;
use OPG\Digideps\Common\Account\PasswordResponse;
use OPG\Digideps\Common\Account\ResetRequest;
use OPG\Digideps\Common\Account\Token;
use OPG\Digideps\Common\Account\ValidationKey;
use Symfony\Component\Uid\Uuid;

class AccountControllerTest extends AbstractTestController
{
    public function testRequestMatchingError(): void
    {
        $email = $this->makeEmail();
        ['client' => $client, 'persons' => ['deputies' => ['lay' => $deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay', hasLogin: false))
        )));
        $decoded = $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname() . 'X'),
            new NonEmptyString($deputy->getAddressPostcode() ?? ''),
            new Email($email),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::MatchingError, $response);
    }

    public function testRequestPasswordSet(): void
    {
        ['client' => $client, 'persons' => ['deputies' => ['lay' => $deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay'))
        )));
        $decoded = $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname()),
            new NonEmptyString($deputy->getAddressPostcode() ?? ''),
            new Email($deputy->getEmail1()),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::PasswordSet, $response);
    }

    public function testRequestAccountTaken(): void
    {
        $email = $this->makeEmail();
        ['client' => $client, 'persons' => ['deputies' => ['lay' => $deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay'))
        )));
        $decoded = $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname()),
            new NonEmptyString($deputy->getAddressPostcode() ?? ''),
            new Email($email),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::AccountTaken, $response);
    }

    public function testRequest(): void
    {
        $email = $this->makeEmail();
        ['client' => $client, 'persons' => ['deputies' => ['lay' => $deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay', hasLogin: false))
        )));
        $decoded = $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname()),
            new NonEmptyString($deputy->getAddressPostcode() ?? ''),
            new Email($email),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountResponse::class, $response);
        $this->assertSame($email, $response->email->value);
    }

    public function testResetUnknownEmail(): void
    {
        $email = $this->makeEmail();
        ['persons' => ['users' => ['lay1' => $user]]] = $this->fixtureService()->instantiateScenario(Scenario::newSimpleLayScenario());

        $decoded = $this->assertJsonRequest('POST', '/account/password/reset', ['data' => new ResetRequest(
            new Email($email),
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::UnknownEmail, $response);
    }

    public function testReset(): void
    {
        ['persons' => ['users' => ['lay1' => $user]]] = $this->fixtureService()->instantiateScenario(Scenario::newSimpleLayScenario());

        $decoded = $this->assertJsonRequest('POST', '/account/password/reset', ['data' => new ResetRequest(
            new Email($user->getEmail()),
        )]);

        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountResponse::class, $response);
        $this->assertSame($user->getEmail(), $response->email->value);
    }

    public function testSetTokenInvalid(): void
    {
        ['persons' => ['users' => ['lay1' => $user]]] = $this->fixtureService()->instantiateScenario(Scenario::newSimpleLayScenario());


        $decoded = $this->assertJsonRequest('POST', '/account/password/set', ['data' => new PasswordRequest(
            new Password('Hello World XXX'),
            new Token(),
            new ValidationKey('12345678901234567890123456789012')
        )]);
        $response = PasswordResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::TokenInvalid, $response);

        $decoded = $this->assertJsonRequest('POST', '/account/password/reset', ['data' => new ResetRequest(
            new Email($user->getEmail()),
        )]);
        $resetResponse = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountResponse::class, $resetResponse);

        $decoded = $this->assertJsonRequest('POST', '/account/password/set', ['data' => new PasswordRequest(
            new Password('Hello World XXX'),
            new Token(),
            $resetResponse->validationKey
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::TokenInvalid, $response);

        $decoded = $this->assertJsonRequest('POST', '/account/password/set', ['data' => new PasswordRequest(
            new Password('Hello World XXX'),
            $resetResponse->token,
            $resetResponse->validationKey
        )]);
        $response = PasswordResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountError::class, $response);
        $this->assertSame(AccountError::TokenInvalid, $response);
    }

    public function testSet(): void
    {
        ['persons' => ['users' => ['lay1' => $user]]] = $this->fixtureService()->instantiateScenario(Scenario::newSimpleLayScenario());

        $decoded = $this->assertJsonRequest('POST', '/account/password/reset', ['data' => new ResetRequest(
            new Email($user->getEmail()),
        )]);

        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountResponse::class, $response);
        $decoded = $this->assertJsonRequest('POST', '/account/password/set', ['data' => new PasswordRequest(
            new Password('Hello World XXX'),
            $response->token,
            $response->validationKey
        )]);
        $response = PasswordResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->login($user->getEmail(), 'Hello World XXX', self::$deputySecret);
    }

    public function testRequestFlow(): void
    {
        $email = $this->makeEmail();
        ['client' => $client, 'persons' => ['deputies' => ['lay' => $deputy]]] = $this->fixtureService()->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(new DeputyDescriptor('lay', hasLogin: false))
        )));
        $decoded = $this->assertJsonRequest('POST', '/account/request', ['data' => new AccountRequest(
            new NonEmptyString($client->getCaseNumber() ?? ''),
            new NonEmptyString($deputy->getFirstname()),
            new NonEmptyString($deputy->getLastname()),
            new NonEmptyString($deputy->getAddressPostcode() ?? ''),
            new Email($email),
            new NonEmptyString($client->getLastname() ?? '')
        )]);
        $response = AccountResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(AccountResponse::class, $response);
        $decoded = $this->assertJsonRequest('POST', '/account/password/set', ['data' => new PasswordRequest(
            new Password('Hello World'),
            $response->token,
            $response->validationKey
        )]);
        $response = PasswordResponse::jsonDeserialize(json_encode($decoded) ?: '');
        $this->assertInstanceOf(PasswordResponse::class, $response);
        $this->login($email, 'Hello World', self::$deputySecret);
    }

    private function makeEmail(): string
    {
        return Uuid::v7()->toRfc4122() . '@test.test';
    }
}
