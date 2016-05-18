<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class AccountController extends RestController
{
    /**
     * @deprecated in favour of report/{id}?group=[accounts]
     * @Route("/report/{id}/accounts")
     * @Method({"GET"})
     */
    public function getAccountsAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array) $request->query->get('groups'));
        }

        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $accounts = $this->getRepository('Account')->findByReport($report, [
            'id' => 'DESC',
        ]);

        if (count($accounts) === 0) {
            return [];
        }

        return $accounts;
    }

    /**
     * @Route("/report/{reportId}/account")
     * @Method({"POST"})
     */
    public function addAccountAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request, [
           'opening_balance' => 'mustExist',
        ]);

        $account = new EntityDir\Account();
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

        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array) $request->query->get('groups'));
        }

        $account = $this->findEntityBy('Account', $id, 'Account not found');
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        return $account;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"PUT"})
     */
    public function editAccountAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $account = $this->findEntityBy('Account', $id, 'Account not found'); /* @var $account EntityDir\Account*/
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $data = $this->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());

        $this->getEntityManager()->flush();

        return $account;
    }

    /**
     * @Route("/account/{id}")
     * @Method({"DELETE"})
     */
    public function accountDelete($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $account = $this->findEntityBy('Account', $id, 'Account not found'); /* @var $account EntityDir\Account */
        $this->denyAccessIfReportDoesNotBelongToUser($account->getReport());

        $this->getEntityManager()->remove($account);

        $this->getEntityManager()->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Account $account, array $data)
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

        if (array_key_exists('opening_date', $data)) {
            $account->setOpeningDate(new \DateTime($data['opening_date']));
        }

        if (array_key_exists('opening_balance', $data)) {
            $account->setOpeningBalance($data['opening_balance']);
        }

        if (array_key_exists('closing_date', $data)) {
            $account->setClosingDate(new \DateTime($data['closing_date']));
        }

        if (array_key_exists('closing_date_explanation', $data)) {
            $account->setClosingDateExplanation($data['closing_date_explanation']);
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
