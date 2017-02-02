<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends RestController
{
    /**
     * @Route("/report/{reportId}/account")
     * @Method({"POST"})
     */
    public function addAccountAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

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
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

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
     */
    public function editAccountAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

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
     */
    public function accountDelete($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $account = $this->findEntityBy(EntityDir\Report\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Report\BankAccount */
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $this->getEntityManager()->remove($account);

        $this->getEntityManager()->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Report\BankAccount $account, array $data)
    {
        //basicdata
        if (array_key_exists('bank', $data)) {
            $account->setBank($data['bank']);
        }

        if (array_key_exists('account_type', $data)) {
            $account->setAccountType($data['account_type']);
        }

        if (array_key_exists('sort_code', $data)) {
            $account->setSortCode($data['sort_code']);
        }

        if (!$account->requiresBankNameAndSortCode()) {
            $account->setBank(null);
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
