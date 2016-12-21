<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\RestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class ExpenseController extends RestController
{
    /**
     * @Route("/odr/{odrId}/expense/{expenseId}", requirements={"odrId":"\d+", "expenseId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($odrId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $expense = $this->findEntityBy('Odr\Expense', $expenseId);
        $this->denyAccessIfOdrDoesNotBelongToUser($expense->getOdr());

        return $expense;
    }

    /**
     * @Route("/odr/{odrId}/expense", requirements={"odrId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $odrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $odr = $this->findEntityBy('Odr\Odr', $odrId); /* @var $odr EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);
        $this->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $expense = new EntityDir\Odr\Expense($odr);

        $this->updateEntityWithData($expense, $data);
        $odr->setPaidForAnything('yes');

        $this->persistAndFlush($expense);
        $this->persistAndFlush($odr);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/odr/{odrId}/expense/{expenseId}", requirements={"odrId":"\d+", "expenseId":"\d+"})
     * @Method({"PUT"})
     */
    public function edit(Request $request, $odrId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $odr = $this->findEntityBy('Odr\Odr', $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $expense = $this->findEntityBy('Odr\Expense', $expenseId);
        $this->denyAccessIfOdrDoesNotBelongToUser($expense->getOdr());

        $this->updateEntityWithData($expense, $data);

        $this->getEntityManager()->flush($expense);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/odr/{odrId}/expense/{expenseId}", requirements={"odrId":"\d+", "expenseId":"\d+"})
     * @Method({"DELETE"})
     */
    public function delete($odrId, $expenseId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $odrId); /* @var $odr EntityDir\Odr\Odr */
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $expense = $this->findEntityBy('Odr\Expense', $expenseId);
        $this->denyAccessIfOdrDoesNotBelongToUser($expense->getOdr());
        $this->getEntityManager()->remove($expense);

        if (count($odr->getExpenses()) === 0) {
            $odr->setPaidForAnything(null); // reset choice
        }
        $this->getEntityManager()->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Odr\Expense $expense, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($expense, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);
    }
}
