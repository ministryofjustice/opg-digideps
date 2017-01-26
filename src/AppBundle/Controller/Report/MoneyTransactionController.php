<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionController extends RestController
{
    /**
     * @Route("/report/{reportId}/money-transaction")
     * @Method({"POST"})
     */
    public function addMoneyTransactionAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'category' => 'notEmpty',
           'amount' => 'notEmpty',
        ]);

        $t = new EntityDir\Report\MoneyTransaction($report);
        $t->setCategory($data['category']);
        $t->setAmount($data['amount']);
        if (array_key_exists('description', $data)) {
            $t->setDescription($data['description']);
        }
        $t->setReport($report);
        $this->getEntityManager()->persist($t);
        $this->getEntityManager()->flush($t);

        $this->persistAndFlush($t);

        $this->setJmsSerialiserGroups(['transaction']);

        return $t;
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}")
     * @Method({"PUT"})
     */
    public function updateMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy('Report\MoneyTransaction', $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        // set data
        $data = $this->deserializeBodyContent($request);
        if (isset($data['description'])) {
            $t->setDescription($data['description']);
        }
        if (isset($data['amount'])) {
            $t->setAmount($data['amount']);
        }

        $this->getEntityManager()->flush();

        return $t;
    }

    /**
     * @Route("/report/{reportId}/money-transaction/{transactionId}")
     * @Method({"DELETE"})
     */
    public function deleteMoneyTransactionAction(Request $request, $reportId, $transactionId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $t = $this->findEntityBy('Report\MoneyTransaction', $transactionId, 'transaction not found'); /* @var $t EntityDir\Report\MoneyTransaction */
        $this->denyAccessIfReportDoesNotBelongToUser($t->getReport());

        $this->getEntityManager()->remove($t);
        $this->getEntityManager()->flush();

        return [];
    }
}
