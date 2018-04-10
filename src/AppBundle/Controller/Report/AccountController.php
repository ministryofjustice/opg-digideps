<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\BusinessRulesException;
use AppBundle\Exception\UnauthorisedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends RestController
{
    /**
     * @Route("/report/{reportId}/account")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAccountAction(Request $request, $reportId)
    {
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'opening_balance' => 'mustExist',
        ]);

        $account = new EntityDir\Report\BankAccount();
        $account->setReport($report);

        $this->fillAccountData($account, $data);

        $this->persistAndFlush($account);

        return ['id' => $account->getId()];
    }

    /**
     * @Route("/report/account/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['account'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $account;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function editAccountAction(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount*/
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $data = $this->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());

        $this->getEntityManager()->flush();

        $this->setJmsSerialiserGroups(['account']);

        return $account;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDelete($id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $this->denyAccessIfAccountHasTransactions($account);

        $this->getEntityManager()->remove($account);

        $this->getEntityManager()->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Report\BankAccount $account, array $data)
    {
        //basicdata
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
            $account->setIsClosed((boolean) $data['is_closed']);
        }

        if (array_key_exists('closing_balance', $data)) {
            $account->setClosingBalance($data['closing_balance']);
        }

        if (array_key_exists('is_joint_account', $data)) {
            $account->setIsJointAccount($data['is_joint_account']);
        }
    }

    /**
     * Check bank account has transactions
     *
     * @param EntityDir\Report\BankAccount $account
     */
    protected function denyAccessIfAccountHasTransactions(EntityDir\Report\BankAccount $account)
    {
        $report = $account->getReport();
        $errors =[];

        $errors = $this->bankAccountAssociated(
            $report,
            $account,
            $report::SECTION_DEPUTY_EXPENSES,
            $report->getExpenses(),
            $errors
        );
        $errors = $this->bankAccountAssociated(
            $report,
            $account,
            $report::SECTION_MONEY_TRANSFERS,
            $report->getMoneyTransfers(),
            $errors
        );
        $errors = $this->bankAccountAssociated(
            $report,
            $account,
            $report::SECTION_GIFTS,
            $report->getGifts(),
            $errors
        );
        $errors = $this->bankAccountAssociated(
            $report,
            $account,
            $report::SECTION_MONEY_IN,
            $report->getMoneyTransactionsIn(),
            $errors
        );
        $errors = $this->bankAccountAssociated(
            $report,
            $account,
            $report::SECTION_MONEY_OUT,
            $report->getMoneyTransactionsOut(),
            $errors
        );

        foreach($errors as $section => $errorCount) {
            if ($errorCount > 0) {
                $e = new BusinessRulesException('report.bankAccount.deleteWithTransactions', 401);
                $e->setData($errors);
                throw $e;
            }
        }
    }

    /**
     * Check transactions are not linked to the bank account we are trying to delete
     *
     * @param EntityDir\Report\Report $report
     * @param EntityDir\Report\BankAccount $account
     * @param $section
     * @param array $transactions
     * @param array $errors
     *
     * @return array $errors
     */
    private function bankAccountAssociated(
        EntityDir\Report\Report $report,
        EntityDir\Report\BankAccount $account,
        $section,
        $transactions = [],
        $errors = []
    ) {
        if ($report->hasSection($section)) {
            if (!empty($transactions)) {
                $errors[$section] = 0;
                foreach ($transactions as $transaction) {
                    // transfers behaves differently
                    if ($section == EntityDir\Report\Report::SECTION_MONEY_TRANSFERS) {
                        if (!empty($transaction->getFrom()->getId()) &&
                            $account === $transaction->getFrom() || ($account === $transaction->getTo()))
                        {
                            $errors[$section]++;
                        }
                    } else {
                        if ($transaction->getBankAccount()->getId() == $account->getId()) {
                            $errors[$section]++;
                        }
                    }
                }
            }
        }

        return $errors;
    }
}
