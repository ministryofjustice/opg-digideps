<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Repository\MoneyTransactionShortRepository;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

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
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short", methods={"POST"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
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

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}", methods={"PUT"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
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

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}", methods={"DELETE"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
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

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}", requirements={"reportId":"\d+", "transactionId":"\d+"}, methods={"GET"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getOneById($reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $record = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId);
        $this->denyAccessIfReportDoesNotBelongToUser($record->getReport());

        $this->formatter->setJmsSerialiserGroups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut']);

        return $record;
    }

    private function fillData(EntityDir\Report\MoneyTransactionShort $t, array $data)
    {
        $t->setDescription($data['description']);
        $t->setAmount($data['amount']);

        if (array_key_exists('date', $data)) {
            $t->setDate($data['date'] ? new \DateTime($data['date']) : null);
        }
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short/soft-delete/{transactionId}", methods={"PUT"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function softDeleteMoneyTransactionShortAction($transactionId)
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

    /**
     * @Route("/report/{reportId}/money-transaction-short/get-soft-delete", methods={"GET"})
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getSoftDeletedMoneyTransactionShortItems($reportId)
    {
        return $this->moneyTransactionShortRepository->retrieveSoftDeleted($reportId);
    }
}
