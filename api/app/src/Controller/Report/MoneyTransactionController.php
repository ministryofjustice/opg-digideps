<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Repository\MoneyTransactionRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionController extends RestController
{
    private array $sectionIds = [
        EntityDir\Report\Report::SECTION_MONEY_IN,
        EntityDir\Report\Report::SECTION_MONEY_OUT,
    ];

    public function __construct(
       private readonly EntityManagerInterface $em,
       private readonly RestFormatter $formatter,
       private readonly MoneyTransactionRepository $moneyTransactionRepository
    ) {
    }

    /**
     * @Route("/report/{reportId}/money-transaction", methods={"POST"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
           'category' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = new EntityDir\Report\MoneyTransaction($report);
        $t->setCategory($data['category']);
        $t->setAmount($data['amount']);
        if (array_key_exists('description', $data)) {
            $t->setDescription($data['description']);
        }

        // update bank account
        $t->setBankAccount(null);
        if (array_key_exists('bank_account_id', $data) && is_numeric($data['bank_account_id'])) {
            $bankAccount = $this->getRepository(
                EntityDir\Report\BankAccount::class
            )->findOneBy(
                [
                    'id' => $data['bank_account_id'],
                    'report' => $report->getId(),
                ]
            );
            if ($bankAccount instanceof EntityDir\Report\BankAccount) {
                $t->setBankAccount($bankAccount);
            }
        }

        $t->setReport($report);

        $this->em->persist($t);
        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}", methods={"PUT"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
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
                $t->setBankAccount($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['bank_account_id']));
            } else {
                $t->setBankAccount(null);
            }
        }
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}", methods={"DELETE"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $this->em->remove($t);
        $this->em->flush();

        // Entity is soft-deletable, so objects need to be removed a second time in order to action hard delete
        $this->em->remove($t);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    /**
     * @Route("/report/{reportId}/money-transaction/soft-delete/{transactionId}", methods={"PUT"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function softDeleteMoneyTransactionAction($transactionId)
    {
        $filter = $this->em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(EntityDir\Report\MoneyTransaction::class);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransaction::class, $transactionId, 'transaction not found');

        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $t->isDeleted() ? $t->setDeletedAt(null) : $t->setDeletedAt(new \DateTime());

        $this->em->flush($t);

        $this->em->getFilters()->enable('softdeleteable');

        return [];
    }

    /**
     * @Route("/report/{reportId}/money-transaction/get-soft-delete", methods={"GET"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getSoftDeletedMoneyTransactionItems($reportId)
    {
        $this->formatter->setJmsSerialiserGroups(['transaction']);

        return $this->moneyTransactionRepository->retrieveSoftDeleted($reportId);
    }
}
