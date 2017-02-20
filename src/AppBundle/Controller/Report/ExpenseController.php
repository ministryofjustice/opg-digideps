<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ExpenseController extends RestController
{
    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $reportId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['expenses'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $expense;
    }

    /**
     * @Route("/report/{reportId}/expense", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
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
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
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
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());
        $this->getEntityManager()->remove($expense);

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
