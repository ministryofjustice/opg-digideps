<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class ExpenseController extends RestController
{
    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($reportId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy('Report\Expense', $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        return $expense;
    }

    /**
     * @Route("/report/{reportId}/expense", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report\Report', $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $expense = new EntityDir\Report\Expense($report);

        $this->updateEntityWithData($expense, $data);
        $report->setPaidForAnything('yes');

        $this->persistAndFlush($expense);
        $this->persistAndFlush($report);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"PUT"})
     */
    public function edit(Request $request, $reportId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy('Report\Expense', $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $this->updateEntityWithData($expense, $data);

        $this->getEntityManager()->flush($expense);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"DELETE"})
     */
    public function delete($reportId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy('Report\Expense', $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());
        $this->getEntityManager()->remove($expense);

        if (count($report->getExpenses()) === 0) {
            $report->setPaidForAnything(null); // reset choice
        }
        $this->getEntityManager()->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Report\Expense $expense, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($expense, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);
    }
}
