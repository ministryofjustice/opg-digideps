<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionShortController extends RestController
{
    /**
     * @Route("/report/{reportId}/money-transaction-short")
     * @Method({"POST"})
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'type' => 'notEmpty',
           'description' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = EntityDir\Report\MoneyTransactionShort::factory($data['type'], $report);
        $this->fillData($t, $data);

        $this->getEntityManager()->persist($t);
        $this->getEntityManager()->flush();

        $this->persistAndFlush($t);

        return $t;
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}")
     * @Method({"PUT"})
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy('Report\MoneyTransactionShort', $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->deserializeBodyContent($request);
        $this->fillData($t, $data);

        $this->getEntityManager()->flush();

        return $t;
    }

    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}")
     * @Method({"DELETE"})
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findReportById($reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy('Report\MoneyTransactionShort', $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());
        $this->getEntityManager()->remove($t);

        $this->getEntityManager()->flush();

        return [];
    }


    /**
     * @Route("/report/{reportId}/money-transaction-short/{transactionId}", requirements={"reportId":"\d+", "transactionId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($reportId, $transactionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $record = $this->findEntityBy('Report\MoneyTransactionShort', $transactionId);
        $this->denyAccessIfReportDoesNotBelongToUser($record->getReport());

        $this->setJmsSerialiserGroups(["transactionsShortIn", "transactionsShortOut"]);

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
