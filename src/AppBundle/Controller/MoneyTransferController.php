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
           'from_account_id' => 'notEmpty',
           'to_account_id' => 'notEmpty',
           'amount' => 'mustExist'
        ]);
        
        $transfer = new EntityDir\MoneyTransfer();
        $transfer->setReport($report);
        $this->fillEntity($transfer, $data);

        $this->persistAndFlush($transfer);
        
        return [ 'id' => $transfer->getId() ];
    }
    
    /**
     * @Route("/report/{reportId}/money-transfers/{transferId}")
     * @Method({"POST"})
     */
    public function editMoneyTransferAction(Request $request, $reportId, $transferId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        $data = $this->deserializeBodyContent($request, [
           'from_account_id' => 'notEmpty',
           'to_account_id' => 'notEmpty',
           'amount' => 'mustExist'
        ]);
        
        $transfer = $this->findEntityBy('MoneyTransfer', $transferId);
        $this->fillEntity($transfer, $data);
        
        $this->persistAndFlush($transfer);
        
        return [ 'id' => $transfer->getId() ];
    }
    
    private function fillEntity(EntityDir\MoneyTransfer $transfer, array $data)
    {
        $transfer    
            ->setFrom($this->findEntityBy('Account', $data['from_account_id']))
            ->setTo($this->findEntityBy('Account', $data['to_account_id']))
            ->setAmount($data['amount']);
    }

}
