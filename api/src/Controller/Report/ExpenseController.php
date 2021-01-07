<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ExpenseController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    private array $sectionIds = [EntityDir\Report\Report::SECTION_DEPUTY_EXPENSES];

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"}, methods={"GET"})
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
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $expense;
    }

    /**
     * @Route("/report/{reportId}/expense", requirements={"reportId":"\d+"}, methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function add(Request $request, $reportId)
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->formatter->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $expense = new EntityDir\Report\Expense($report);

        $this->updateEntityWithData($report, $expense, $data);
        $report->setPaidForAnything('yes');

        $this->em->persist($expense);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);

        $this->em->persist($report);
        $this->em->flush();

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"}, methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function edit(Request $request, $reportId, $expenseId)
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $this->updateEntityWithData($report, $expense, $data);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $expense->getId()];
    }

    /**
     * @Route("/report/{reportId}/expense/{expenseId}", requirements={"reportId":"\d+", "expenseId":"\d+"}, methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function delete($reportId, $expenseId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(EntityDir\Report\Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());
        $this->em->remove($expense);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

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
