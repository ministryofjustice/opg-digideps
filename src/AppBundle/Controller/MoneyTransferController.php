<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class MoneyTransferController extends RestController
{
    /**
     * @Route("/report/{reportId}/money-transfers")
     * @Method({"POST"})
     */
    public function addMoneyTransferAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'account_from_id' => 'notEmpty',
           'account_to_id' => 'notEmpty',
           'amount' => 'mustExist',
        ]);

        $transfer = new EntityDir\MoneyTransfer();
        $transfer->setReport($report);
        $report->setNoTransfersToAdd(null);
        $this->fillEntity($transfer, $data);

        $this->persistAndFlush($transfer);
        $this->persistAndFlush($report);

        $this->setJmsSerialiserGroups(['money-transfer']);

        return $transfer->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}")
     * @Method({"PUT"})
     */
    public function editMoneyTransferAction(Request $request, $reportId, $transferId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'account_from_id' => 'notEmpty',
           'account_to_id' => 'notEmpty',
           'amount' => 'mustExist',
        ]);

        $transfer = $this->findEntityBy('MoneyTransfer', $transferId);
        $this->fillEntity($transfer, $data);

        $this->persistAndFlush($transfer);

        return $transfer->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}")
     * @Method({"DELETE"})
     */
    public function deleteMoneyTransferAction(Request $request, $reportId, $transferId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $transfer = $this->findEntityBy('MoneyTransfer', $transferId);
        $this->denyAccessIfReportDoesNotBelongToUser($transfer->getReport());

        $report->setNoTransfersToAdd(null);

        $this->getEntityManager()->remove($transfer);
        $this->getEntityManager()->flush();

        return [];
    }

    private function fillEntity(EntityDir\MoneyTransfer $transfer, array $data)
    {
        $transfer
            ->setFrom($this->findEntityBy('Account', $data['account_from_id']))
            ->setTo($this->findEntityBy('Account', $data['account_to_id']))
            ->setAmount($data['amount']);
    }
}
