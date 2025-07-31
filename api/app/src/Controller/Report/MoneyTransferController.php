<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MoneyTransferController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_MONEY_TRANSFERS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/money-transfers', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function addMoneyTransfer(Request $request, int $reportId): int
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
           'account_from_id' => 'notEmpty',
           'account_to_id' => 'notEmpty',
           'amount' => 'mustExist',
        ]);

        $transfer = new EntityDir\Report\MoneyTransfer();
        $transfer->setReport($report);

        if (array_key_exists('description', $data)) {
            $transfer->setDescription($data['description']);
        }

        $report->setNoTransfersToAdd(false);

        $this->fillEntity($transfer, $data);

        $this->em->persist($transfer);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        $this->formatter->setJmsSerialiserGroups(['money-transfer']);

        return $transfer->getId();
    }

    #[Route(path: '/report/{reportId}/money-transfers/{transferId}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function editMoneyTransfer(Request $request, int $reportId, int $transferId): int
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
           'account_from_id' => 'notEmpty',
           'account_to_id' => 'notEmpty',
           'amount' => 'mustExist',
        ]);

        $transfer = $this->findEntityBy(EntityDir\Report\MoneyTransfer::class, $transferId);

        if (array_key_exists('description', $data)) {
            $transfer->setDescription($data['description']);
        }

        $this->fillEntity($transfer, $data);

        $this->em->persist($transfer);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $transfer->getId();
    }

    #[Route(path: '/report/{reportId}/money-transfers/{transferId}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteMoneyTransfer(int $reportId, int $transferId): array
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $transfer = $this->findEntityBy(EntityDir\Report\MoneyTransfer::class, $transferId);
        $this->denyAccessIfReportDoesNotBelongToUser($transfer->getReport());

        $this->em->remove($transfer);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function fillEntity(EntityDir\Report\MoneyTransfer $transfer, array $data): void
    {
        $amountCleaned = preg_replace('/[^\d\.]+/', '', $data['amount']); // 123,123.34 -> 123123.34

        $transfer
            ->setFrom($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['account_from_id']))
            ->setTo($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['account_to_id']))
            ->setAmount($amountCleaned);
    }
}
