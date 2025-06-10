<?php

declare(strict_types=1);

namespace app\tests\Integration\v2\Registration\DeputyshipProcessing;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\v2\Registration\DeputyshipProcessing\DeputyshipCandidatesGroup;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use App\v2\Service\DeputyshipCandidatesConverter;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyshipCandidateConverterIntegrationTest extends KernelTestCase
{
    private EntityManager $entityManager;
    private ORMPurger $purger;
    private DeputyshipCandidatesConverter $sut;

    protected function setUp(): void
    {
        $container = self::bootKernel()->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        $this->purger = new ORMPurger($this->entityManager);

        /** @var DeputyshipCandidatesConverter $sut */
        $sut = $container->get(DeputyshipCandidatesConverter::class);
        $this->sut = $sut;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->purger->purge();
    }

    // TODO if we have multiple court orders with the same NDR, this causes a unique key violation, and also prevents subsequent rows from being processed
    public function testMultipleCourtOrdersWithSameNdr(): void
    {
        $caseNumber = '1122334455';
        $orderUid1 = '78866434545';
        $orderUid2 = '79996434666';
        $orderUid3 = '71112224333';

        // add client and associated NDR to db
        $client = new Client();
        $client->setCaseNumber($caseNumber);
        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $ndr = new Ndr($client);
        $this->entityManager->persist($ndr);
        $this->entityManager->flush();

        // candidate group 1: court order for case number
        // candidates: add court order A, add court order ndr for A
        $candidatesGroup1 = new DeputyshipCandidatesGroup();
        $candidatesGroup1->orderUid = $orderUid1;
        $candidatesGroup1->insertOrder = [
            'action' => DeputyshipCandidateAction::InsertOrder,
            'orderUid' => $orderUid1,
            'orderType' => 'pfa',
            'status' => 'ACTIVE',
            'orderMadeDate' => '2022-06-10',
        ];
        $candidatesGroup1->insertOthers = [
            ['action' => DeputyshipCandidateAction::InsertOrderNdr, 'orderUid' => $orderUid1, 'ndrId' => $ndr->getId()],
        ];

        // candidate group 2: different court order for same case number - currently fails due to unique key violation
        // candidates: add court order B, add court order ndr for B
        $candidatesGroup2 = new DeputyshipCandidatesGroup();
        $candidatesGroup2->orderUid = $orderUid2;
        $candidatesGroup2->insertOrder = [
            'action' => DeputyshipCandidateAction::InsertOrder,
            'orderUid' => $orderUid2,
            'orderType' => 'pfa',
            'status' => 'ACTIVE',
            'orderMadeDate' => '2025-06-10',
        ];
        $candidatesGroup2->insertOthers = [
            ['action' => DeputyshipCandidateAction::InsertOrderNdr, 'orderUid' => $orderUid2, 'ndrId' => $ndr->getId()],
        ];

        // candidate group 3: separate court order for different client - also currently fails even though it's unrelated, due to transaction breakage on group 2
        // candidates: add court order C
        $candidatesGroup3 = new DeputyshipCandidatesGroup();
        $candidatesGroup3->orderUid = $orderUid3;
        $candidatesGroup3->insertOrder = [
            'action' => DeputyshipCandidateAction::InsertOrder,
            'orderUid' => $orderUid3,
            'orderType' => 'pfa',
            'status' => 'ACTIVE',
            'orderMadeDate' => '2025-06-10',
        ];

        $result = $this->sut->convert($candidatesGroup1, dryRun: false);
        print_r($result);

        echo "++++++++++++++++++++++++++\n";

        $result = $this->sut->convert($candidatesGroup2, dryRun: false);
        print_r($result);

        echo "++++++++++++++++++++++++++\n";

        $result = $this->sut->convert($candidatesGroup3, dryRun: false);
        print_r($result);
    }
}
