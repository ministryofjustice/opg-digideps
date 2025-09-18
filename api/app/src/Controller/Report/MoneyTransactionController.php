<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\BankAccount;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\Report;
use App\Repository\MoneyTransactionRepository;
use App\Service\Formatter\RestFormatter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MoneyTransactionController extends RestController
{
    private array $sectionIds = [
        Report::SECTION_MONEY_IN,
        Report::SECTION_MONEY_OUT,
    ];

    public function __construct(
       private readonly EntityManagerInterface $em,
       private readonly RestFormatter $formatter,
       private readonly MoneyTransactionRepository $moneyTransactionRepository
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/money-transaction', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function addMoneyTransaction(Request $request, int $reportId): int
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
           'category' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = new MoneyTransaction($report);
        $t->setCategory($data['category']);
        $t->setAmount($data['amount']);
        if (array_key_exists('description', $data)) {
            $t->setDescription($data['description']);
        }

        // update bank account
        $t->setBankAccount(null);
        if (array_key_exists('bank_account_id', $data) && is_numeric($data['bank_account_id'])) {
            $bankAccount = $this->em->getRepository(
                BankAccount::class
            )->findOneBy(
                [
                    'id' => $data['bank_account_id'],
                    'report' => $report->getId(),
                ]
            );
            if ($bankAccount instanceof BankAccount) {
                $t->setBankAccount($bankAccount);
            }
        }

        $t->setReport($report);

        $this->em->persist($t);
        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    #[Route(path: '/report/{reportId}/money-transaction/{transactionId}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function updateMoneyTransaction(Request $request, int $reportId, int $transactionId): int
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t \App\Entity\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->formatter->deserializeBodyContent($request);
        if (isset($data['description'])) {
            $t->setDescription($data['description']);
        }
        if (isset($data['amount'])) {
            $t->setAmount($data['amount']);
        }

        if (array_key_exists('bank_account_id', $data)) {
            if (is_numeric($data['bank_account_id'])) {
                $t->setBankAccount($this->findEntityBy(BankAccount::class, $data['bank_account_id']));
            } else {
                $t->setBankAccount(null);
            }
        }
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    #[Route(path: '/report/{reportId}/money-transaction/{transactionId}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteMoneyTransaction(int $reportId, int $transactionId): array
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(MoneyTransaction::class, $transactionId, 'transaction not found');
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // Entity is soft-deletable, so set the DeletedAt to hard delete
        $t->setDeletedAt(new DateTime());
        $this->em->remove($t);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    #[Route(path: '/report/{reportId}/money-transaction/soft-delete/{transactionId}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function softDeleteMoneyTransaction(int $transactionId): array
    {
        $filter = $this->em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(MoneyTransaction::class);

        $t = $this->findEntityBy(MoneyTransaction::class, $transactionId, 'transaction not found');

        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $t->isDeleted() ? $t->setDeletedAt(null) : $t->setDeletedAt(new DateTime());

        $this->em->flush($t);

        $this->em->getFilters()->enable('softdeleteable');

        return [];
    }

    #[Route(path: '/report/{reportId}/money-transaction/get-soft-delete', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getSoftDeletedMoneyTransactionItems(int $reportId): array
    {
        $this->formatter->setJmsSerialiserGroups(['transaction']);

        return $this->moneyTransactionRepository->retrieveSoftDeleted($reportId);
    }
}
