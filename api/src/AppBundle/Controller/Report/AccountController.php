<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report as Report;
use AppBundle\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    private $sectionIds = [Report::SECTION_BANK_ACCOUNTS];

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/report/{reportId}/account", methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAccountAction(Request $request, $reportId)
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

    /**
     * @Route("/report/account/{id}", methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['account'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $account;
    }

    /**
     * @Route("/account/{id}", methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function editAccountAction(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount*/
        $report = $account->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $data = $this->formatter->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        $this->formatter->setJmsSerialiserGroups(['account']);

        return $account;
    }

    /**
     * @Route("/account/{id}/dependent-records", methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDependentRecords($id)
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
                    ?  count($report->getExpenses()->filter($paymentsFilter))
                    : null,
                Report::SECTION_GIFTS => $report->hasSection(Report::SECTION_GIFTS)
                    ? count($report->getGifts()->filter($paymentsFilter))
                    : null,
                Report::SECTION_MONEY_IN => $report->hasSection(Report::SECTION_MONEY_IN)
                   ? count($report->getMoneyTransactionsIn()->filter($paymentsFilter))
                    : null,
                Report::SECTION_MONEY_OUT => $report->hasSection(Report::SECTION_MONEY_OUT)
                    ? count($report->getMoneyTransactionsOut()->filter($paymentsFilter))
                    : null
            ]
        ];

        $ret['transactionsCount'] = array_sum($ret['transactions']);

        return $ret;
    }

    /**
     * @Route("/account/{id}", methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDelete($id)
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
}
