<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionShortController extends RestController
{
    /**
     * @Route("/report/{reportId}/money-transaction-short")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'type' => 'notEmpty',
           'description' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = EntityDir\Report\MoneyTransactionShort::factory($data['type'], $report);
        $this->fillData($t, $data);

        $this->getEntityManager()->persist($t);

        $report->updateSectionStatus(EntityDir\Report\Report::SECTION_MONEY_IN_SHORT);
        $report->updateSectionStatus(EntityDir\Report\Report::SECTION_MONEY_OUT_SHORT);

        $this->getEntityManager()->flush();

        $this->persistAndFlush($t);

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->deserializeBodyContent($request);
        $this->fillData($t, $data);

        $report->updateSectionStatus(EntityDir\Report\Report::SECTION_MONEY_IN_SHORT);
        $report->updateSectionStatus(EntityDir\Report\Report::SECTION_MONEY_OUT_SHORT);

        $this->getEntityManager()->flush();

        return $t->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());
        $this->getEntityManager()->remove($t);

        $report->updateSectionStatus(EntityDir\Report\Report::SECTION_MONEY_IN_SHORT);
        $report->updateSectionStatus(EntityDir\Report\Report::SECTION_MONEY_OUT_SHORT);

        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}", requirements={"reportId":"\d+", "transactionId":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById($reportId, $transactionId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $record = $this->findEntityBy(EntityDir\Report\MoneyTransactionShort::class, $transactionId);
        $this->denyAccessIfReportDoesNotBelongToUser($record->getReport());

        $this->setJmsSerialiserGroups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut']);

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
}
