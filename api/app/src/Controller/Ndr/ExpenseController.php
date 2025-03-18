<?php

namespace App\Controller\Ndr;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ExpenseController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
    }

    /**
     * @Route("/ndr/{ndrId}/expense/{expenseId}", requirements={"ndrId":"\d+", "expenseId":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function getOneById($ndrId, $expenseId)
    {
        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $expense = $this->findEntityBy(EntityDir\Ndr\Expense::class, $expenseId);
        $this->denyAccessIfNdrDoesNotBelongToUser($expense->getNdr());

        $this->formatter->setJmsSerialiserGroups(['ndr-expenses']);

        return $expense;
    }

    /**
     * @Route("/ndr/{ndrId}/expense", requirements={"ndrId":"\d+"}, methods={"POST"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function add(Request $request, $ndrId)
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId); /* @var $ndr EntityDir\Ndr\Ndr */
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);
        $this->formatter->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $expense = new EntityDir\Ndr\Expense($ndr);

        $this->updateEntityWithData($expense, $data);
        $ndr->setPaidForAnything('yes');

        $this->em->persist($expense);
        $this->em->persist($ndr);
        $this->em->flush();

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/ndr/{ndrId}/expense/{expenseId}", requirements={"ndrId":"\d+", "expenseId":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function edit(Request $request, $ndrId, $expenseId)
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $expense = $this->findEntityBy(EntityDir\Ndr\Expense::class, $expenseId);
        $this->denyAccessIfNdrDoesNotBelongToUser($expense->getNdr());

        $this->updateEntityWithData($expense, $data);

        $this->em->flush($expense);

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/ndr/{ndrId}/expense/{expenseId}", requirements={"ndrId":"\d+", "expenseId":"\d+"}, methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_DEPUTY')")
     */
    public function delete($ndrId, $expenseId)
    {
        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId); /* @var $ndr EntityDir\Ndr\Ndr */
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $expense = $this->findEntityBy(EntityDir\Ndr\Expense::class, $expenseId);
        $this->denyAccessIfNdrDoesNotBelongToUser($expense->getNdr());
        $this->em->remove($expense);

        $this->em->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Ndr\Expense $expense, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($expense, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);
    }
}
