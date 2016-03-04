<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;

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
           'accountFrom' => 'notEmpty',
           'accountTo' => 'notEmpty',
           'amount' => 'mustExist'
        ]);
        
        $transfer = new EntityDir\MoneyTransfer();
        $transfer->setReport($report);
        $this->fillEntity($transfer, $data);

        $this->persistAndFlush($transfer);
        
        $this->setJmsSerialiserGroups(['transfers']);
        
        return $transfer;
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
           'accountFrom' => 'notEmpty',
           'accountTo' => 'notEmpty',
           'amount' => 'mustExist'
        ]);
        
        $transfer = $this->findEntityBy('MoneyTransfer', $transferId);
        $this->fillEntity($transfer, $data);
        
        $this->persistAndFlush($transfer);
        
        return $transfer;
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
        
        $this->getEntityManager()->remove($transfer);
        $this->getEntityManager()->flush($transfer);

        return [];
    }
    
    private function fillEntity(EntityDir\MoneyTransfer $transfer, array $data)
    {
        $transfer    
            ->setFrom($this->findEntityBy('Account', $data['accountFrom']['id']))
            ->setTo($this->findEntityBy('Account', $data['accountTo']['id']))
            ->setAmount($data['amount']);
    }

}
