<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ExpenseController extends RestController
{
    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $reportId, $expenseId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['expenses', 'account'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $expense;
    }

    /**
     * @Route("/report/{reportId}/expense", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request, $reportId)
    {
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $expense = new EntityDir\Report\Expense($report);

        $this->updateEntityWithData($report, $expense, $data);
        $report->setPaidForAnything('yes');

        $this->persistAndFlush($expense);
        $this->persistAndFlush($report);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function edit(Request $request, $reportId, $expenseId)
    {
        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $this->updateEntityWithData($report, $expense, $data);

        $this->getEntityManager()->flush($expense);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"})
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function delete($reportId, $expenseId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());
        $this->getEntityManager()->remove($expense);

        $this->getEntityManager()->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Report\Report $report, EntityDir\Report\Expense $expense, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($expense, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);

        // update bank account
        $expense->setBankAccount(null);
        if (array_key_exists('bank_account_id', $data) && is_numeric($data['bank_account_id'])) {
            $bankAccount = $this->getRepository(
                EntityDir\Report\BankAccount::class
            )->findOneBy(
                [
                    'id' => $data['bank_account_id'],
                    'report' => $report->getId()
                ]
            );
            if ($bankAccount instanceof EntityDir\Report\BankAccount) {
                $expense->setBankAccount($bankAccount);
            }
        }
    }
}
