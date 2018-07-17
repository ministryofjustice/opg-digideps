<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransferController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_MONEY_TRANSFERS];

    /**
     * @Route("/report/{reportId}/money-transfers")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addMoneyTransferAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'account_from_id' => 'notEmpty',
           'account_to_id' => 'notEmpty',
           'amount' => 'mustExist',
        ]);

        $transfer = new EntityDir\Report\MoneyTransfer();
        $transfer->setReport($report);
        $report->setNoTransfersToAdd(null);
        $this->fillEntity($transfer, $data);

        $this->persistAndFlush($transfer);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        $this->setJmsSerialiserGroups(['money-transfer']);

        return $transfer->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function editMoneyTransferAction(Request $request, $reportId, $transferId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'account_from_id' => 'notEmpty',
           'account_to_id' => 'notEmpty',
           'amount' => 'mustExist',
        ]);

        $transfer = $this->findEntityBy(EntityDir\Report\MoneyTransfer::class, $transferId);
        $this->fillEntity($transfer, $data);

        $this->persistAndFlush($transfer);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return $transfer->getId();
    }

    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteMoneyTransferAction(Request $request, $reportId, $transferId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $transfer = $this->findEntityBy(EntityDir\Report\MoneyTransfer::class, $transferId);
        $this->denyAccessIfReportDoesNotBelongToUser($transfer->getReport());

        $this->getEntityManager()->remove($transfer);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    private function fillEntity(EntityDir\Report\MoneyTransfer $transfer, array $data)
    {
        $amountCleaned = preg_replace('/[^\d\.]+/', '', $data['amount']); // 123,123.34 -> 123123.34

        $transfer
            ->setFrom($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['account_from_id']))
            ->setTo($this->findEntityBy(EntityDir\Report\BankAccount::class, $data['account_to_id']))
            ->setAmount($amountCleaned);
    }
}
