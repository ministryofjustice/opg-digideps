<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Entity\Report\Report;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountController extends RestController
{
    private array $sectionIds = [Report::SECTION_BANK_ACCOUNTS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/account', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function addAccount(Request $request, int $reportId): array
    {
        $report = $this->findEntityBy(Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->formatter->deserializeBodyContent($request, [
           'opening_balance' => 'mustExist',
        ]);

        $account = new EntityDir\Report\BankAccount();
        $account->setReport($report);

        $this->fillAccountData($account, $data);

        $this->em->persist($account);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $account->getId()];
    }

    #[Route(path: '/report/account/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): EntityDir\Report\BankAccount
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['account'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $account;
    }

    #[Route(path: '/account/{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function editAccount(Request $request, int $id): EntityDir\Report\BankAccount
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $report = $account->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $data = $this->formatter->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        $this->formatter->setJmsSerialiserGroups(['account']);

        return $account;
    }

    #[Route(path: '/account/{id}/dependent-records', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function accountDependentRecords(int $id): array
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $report = $account->getReport();

        $transferFilter = function ($transfer) use ($account) {
            return $transfer->getFrom() === $account || $transfer->getTo() === $account;
        };

        $paymentsFilter = function ($expense) use ($account) {
            return $expense->getBankAccount() === $account;
        };

        $ret = [
            Report::SECTION_MONEY_TRANSFERS => $report->hasSection(Report::SECTION_MONEY_TRANSFERS)
                ? count($report->getMoneyTransfers()->filter($transferFilter))
                : null,
            'transactions' => [
                Report::SECTION_DEPUTY_EXPENSES => $report->hasSection(Report::SECTION_DEPUTY_EXPENSES)
                    ? count($report->getExpenses()->filter($paymentsFilter))
                    : null,
                Report::SECTION_GIFTS => $report->hasSection(Report::SECTION_GIFTS)
                    ? count($report->getGifts()->filter($paymentsFilter))
                    : null,
                Report::SECTION_MONEY_IN => $report->hasSection(Report::SECTION_MONEY_IN)
                   ? count($report->getMoneyTransactionsIn()->filter($paymentsFilter))
                    : null,
                Report::SECTION_MONEY_OUT => $report->hasSection(Report::SECTION_MONEY_OUT)
                    ? count($report->getMoneyTransactionsOut()->filter($paymentsFilter))
                    : null,
            ],
        ];

        $ret['transactionsCount'] = array_sum($ret['transactions']);

        return $ret;
    }

    #[Route(path: '/account/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function accountDelete(int $id): array
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $report = $account->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->em->remove($account);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Report\BankAccount $account, array $data): void
    {
        // basicdata
        if (array_key_exists('account_type', $data)) {
            $account->setAccountType($data['account_type']);
        }

        if ($account->requiresBankName()) {
            if (array_key_exists('bank', $data)) {
                $account->setBank($data['bank']);
            }
        } else {
            $account->setBank(null);
        }

        if ($account->requiresSortCode()) {
            if (array_key_exists('sort_code', $data)) {
                $account->setSortCode($data['sort_code']);
            }
        } else {
            $account->setSortCode(null);
        }

        if (array_key_exists('account_number', $data)) {
            $account->setAccountNumber($data['account_number']);
        }

        if (array_key_exists('opening_balance', $data)) {
            $account->setOpeningBalance($data['opening_balance']);
        }

        if (array_key_exists('is_closed', $data)) {
            $account->setIsClosed((bool) $data['is_closed']);
        }

        if (array_key_exists('closing_balance', $data)) {
            $account->setClosingBalance($data['closing_balance']);
        }

        if (array_key_exists('is_joint_account', $data)) {
            $account->setIsJointAccount($data['is_joint_account']);
        }
    }
}
