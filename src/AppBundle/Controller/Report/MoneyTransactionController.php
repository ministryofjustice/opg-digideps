<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class MoneyTransactionController extends RestController
{
    /**
     * @Route("/report/{reportId}/money-transaction")
     * @Method({"PUT"})
     */
    public function editMoneyTransactionAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'id' => 'notEmpty',
           'amounts' => 'notEmpty',
        ]);

        $t = $report->getTransactionByTypeId($data['id']);
        /* @var $t EntityDir\Report\Transaction */
        if (!$t instanceof EntityDir\Report\Transaction) {
            throw new \InvalidArgumentException('');
        }
        $t->setAmounts($data['amounts'] ?: []);
        if (array_key_exists('more_details', $data)) {
            $t->setMoreDetails($data['more_details']);
        }
        $this->getEntityManager()->flush($t);
        $t->setReport($report);

        $this->persistAndFlush($t);

        $this->setJmsSerialiserGroups(['transaction']);

        return $t->getId();
    }
}
