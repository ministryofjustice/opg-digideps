<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Expense;
use App\Entity\Report\Report;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExpenseController extends RestController
{
    private array $sectionIds = [Report::SECTION_DEPUTY_EXPENSES];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/expense/{expenseId}', requirements: ['reportId' => '\d+', 'expenseId' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $reportId, int $expenseId): Expense
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['expenses', 'account'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $expense;
    }

    #[Route(path: '/report/{reportId}/expense', requirements: ['reportId' => '\d+'], methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function add(Request $request, int $reportId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(Report::class, $reportId); /* @var $report \App\Entity\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->formatter->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $expense = new Expense($report);

        $this->updateEntityWithData($report, $expense, $data);
        $report->setPaidForAnything('yes');

        $this->em->persist($expense);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);

        $this->em->persist($report);
        $this->em->flush();

        return ['id' => $expense->getId()];
    }

    #[Route(path: '/report/{reportId}/expense/{expenseId}', requirements: ['reportId' => '\d+', 'expenseId' => '\d+'], methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function edit(Request $request, int $reportId, int $expenseId): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());

        $this->updateEntityWithData($report, $expense, $data);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $expense->getId()];
    }

    #[Route(path: '/report/{reportId}/expense/{expenseId}', requirements: ['reportId' => '\d+', 'expenseId' => '\d+'], methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function delete(int $reportId, int $expenseId): array
    {
        $report = $this->findEntityBy(Report::class, $reportId); /* @var $report \App\Entity\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $expense = $this->findEntityBy(Expense::class, $expenseId);
        $this->denyAccessIfReportDoesNotBelongToUser($expense->getReport());
        $this->em->remove($expense);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function updateEntityWithData(
        Report $report,
        Expense $expense, array $data
    ): void {
        // common props
        $this->hydrateEntityWithArrayData($expense, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);

        // update bank account
        $expense->setBankAccount(null);
        if (array_key_exists('bank_account_id', $data) && is_numeric($data['bank_account_id'])) {
            $bankAccount = $this->em->getRepository(
                BankAccount::class
            )->findOneBy(
                [
                    'id' => $data['bank_account_id'],
                    'report' => $report->getId(),
                ]
            );
            if ($bankAccount instanceof BankAccount) {
                $expense->setBankAccount($bankAccount);
            }
        }
    }
}
