<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Client;
use App\Entity\StagingDeputyship;
use App\Entity\User;
use App\Service\DeputyCaseService;
use App\Tests\Integration\ApiBaseTestCase;

class DeputyCaseServiceIntegrationTest extends ApiBaseTestCase
{
    private DeputyCaseService $sut;

    public function setUp(): void
    {
        parent::setUp();

        /** @var DeputyCaseService $sut */
        $sut = $this->container->get(DeputyCaseService::class);
        $this->sut = $sut;
    }

    public function testCreate(): void
    {
        $courtOrderUid = '71112223';
        $caseNumber = '93015923';
        $deputyUid = '89893420';

        // row in staging.deputyship associating deputy UID with case number
        $deputyship = new StagingDeputyship();
        $deputyship->orderUid = $courtOrderUid;
        $deputyship->caseNumber = $caseNumber;
        $deputyship->deputyUid = $deputyUid;

        $this->entityManager->persist($deputyship);

        // client with same case number
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->entityManager->persist($client);

        // dd_user with same deputy UID;
        // note there's no association in deputy_case between client and dd_user
        $user = new User();
        $user->setDeputyUid(intval($deputyUid))
            ->setFirstname('Bill')
            ->setLastname('Sykes')
            ->setPassword('')
            ->setEmail('bill.sykes-deputy-case-candidate@opg.gov.uk');

        $this->entityManager->persist($user);

        $this->entityManager->flush();

        // we should get a candidate deputy_case for this client and dd_user pair
        $candidates = iterator_to_array($this->sut->addMissingDeputyCaseAssociations());

        self::assertCount(1, $candidates);

        $candidate = $candidates[0];
        self::assertEquals($courtOrderUid, $candidate['order_uid']);
        self::assertEquals($user->getId(), $candidate['user_id']);
        self::assertEquals($client->getId(), $candidate['client_id']);
    }
}
