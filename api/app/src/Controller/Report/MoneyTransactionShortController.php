<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Repository\MoneyTransactionShortRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MoneyTransactionShortController extends RestController
{
    private $sectionIds = [
        EntityDir\Report\Report::SECTION_MONEY_IN_SHORT,
        EntityDir\Report\Report::SECTION_MONEY_OUT_SHORT,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RestFormatter $formatter,
        private readonly MoneyTransactionShortRepository $moneyTransactionShortRepository
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/money-transaction-short', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function addMoneyTransaction(Request $request, int $reportId): int
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
           'type' => 'notEmpty',
           'description' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = EntityDir\Report\MoneyTransactionShort::factory($data['type'], $report);
        $this->fillData($t, $data);

        $this->em->persist($t);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        $this->em->persist($t);
        $this->em->flush();

        return $t->getId();
    }

    #[Route(path: '/report/{reportId}/money-transaction-short/{transactionId}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function updateMoneyTransaction(Request $request, int $reportId, int $transactionId): int
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->formatter->deserializeBodyContent($request);
        $this->fillData($t, $data);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    #[Route(path: '/report/{reportId}/money-transaction-short/{transactionId}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteMoneyTransaction(int $reportId, int $transactionId): array
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $t->setDeletedAt(new \DateTime());
        $this->em->flush();

        // Entity is soft-deletable, so objects need to be removed a second time in order to action hard delete
        $this->em->remove($t);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    #[Route(path: '/report/{reportId}/money-transaction-short/{transactionId}', requirements: ['reportId' => '\d+', 'transactionId' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(int $reportId, int $transactionId): EntityDir\Report\MoneyTransactionShort
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $record = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId);
        $this->denyAccessIfReportDoesNotBelongToUser($record->getReport());

        $this->formatter->setJmsSerialiserGroups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut']);

        return $record;
    }

    private function fillData(EntityDir\Report\MoneyTransactionShort $t, array $data): void
    {
        $t->setDescription($data['description']);
        $t->setAmount($data['amount']);

        if (array_key_exists('date', $data)) {
            $t->setDate($data['date'] ? new \DateTime($data['date']) : null);
        }
    }

    #[Route(path: '/report/{reportId}/money-transaction-short/soft-delete/{transactionId}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function softDeleteMoneyTransactionShort(int $transactionId): array
    {
        $filter = $this->em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(EntityDir\Report\MoneyTransactionShort::class);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId, 'transaction not found');

        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $t->isDeleted() ? $t->setDeletedAt(null) : $t->setDeletedAt(new \DateTime());

        $this->em->flush($t);

        $this->em->getFilters()->enable('softdeleteable');

        return [];
    }

    #[Route(path: '/report/{reportId}/money-transaction-short/get-soft-delete', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getSoftDeletedMoneyTransactionShortItems(int $reportId): array
    {
        return $this->moneyTransactionShortRepository->retrieveSoftDeleted($reportId);
    }
}
