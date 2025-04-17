<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Controller;

use App\Entity\Deputy;
use App\Entity\User;
use App\Tests\Integration\Controller\AbstractTestController;

class CourtOrderControllerTest extends AbstractTestController
{
    public function testGetByUidActionNoAuthFail()
    {
        $this->assertEndpointNeedsAuth('GET', '/v2/courtorder/71101111');
    }

    public function testGetByUidActionCourtOrderNotFoundFail(): void
    {
        // log in, but fetch court order which doesn't exist
    }

    public function testGetByUidActionNotADeputyOnCourtOrderFail(): void
    {
        // add a court order, but don't make the logged in user a deputy on it
    }

    public function testGetByUidActionSuccess(): void
    {
        $fixtures = self::fixtures();

        // add a court order, and make the user a deputy on it
        $courtOrder = $fixtures->createCourtOrder(7747728317, 'pfa', true);
        $fixtures->persist($courtOrder);
        $fixtures->flush();

        // user
        /** @var User $user */
        $user = $fixtures->getRepo(User::class)->findOneByEmail('deputy@example.org');

        $fixtures->persist($user);
        $fixtures->flush();

        // deputy
        $deputy = new Deputy();
        $deputyUid = 748723 + rand(1, 99999);
        $deputy->setEmail1('deputy@example.org');
        $deputy->setDeputyUid($deputyUid);
        $deputy->setFirstname('name'.time());
        $deputy->setLastname('surname'.time());

        $deputy->setUser($user);

        $deputy->associateWithCourtOrder($courtOrder);

        $fixtures->persist($deputy);
        $fixtures->flush();

        // login to get the token for API calls
        $token = $this->loginAsDeputy();

        // make the API call
        $this->assertJsonRequest(
            'GET',
            "/v2/courtorder/{$courtOrder->getCourtOrderUid()}",
            ['AuthToken' => $token, 'mustSucceed' => true]
        );
    }
}
