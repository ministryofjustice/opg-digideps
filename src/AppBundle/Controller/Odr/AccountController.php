<?php

namespace AppBundle\Controller\Odr;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Controller\RestController;

class AccountController extends RestController
{
    /**
     * @deprecated in favour of odr/{id}?group=[accounts]
     * @Route("/odr/{id}/accounts")
     * @Method({"GET"})
     */
    public function getAccountsAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array) $request->query->get('groups'));
        }

        $odr = $this->findEntityBy('Odr\Odr', $id);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $accounts = $this->getRepository('Odr\Account')->findByOdr($odr, [
            'id' => 'DESC',
        ]);

        if (count($accounts) === 0) {
            return [];
        }

        return $accounts;
    }

    /**
     * @Route("/odr/{odrId}/account")
     * @Method({"POST"})
     */
    public function addAccountAction(Request $request, $odrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $odr = $this->findEntityBy('Odr\Odr', $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $data = $this->deserializeBodyContent($request, [
        ]);

        $account = new EntityDir\Odr\Account();
        $account->setOdr($odr);

        $this->fillAccountData($account, $data);

        $this->persistAndFlush($account);

        return ['id' => $account->getId()];
    }

    /**
     * @Route("/odr/account/{id}")
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array) $request->query->get('groups'));
        }

        $account = $this->findEntityBy('Odr\Account', $id, 'Account not found');
        $this->denyAccessIfOdrDoesNotBelongToUser($account->getOdr());

        return $account;
    }

    /**
     * @Route("/odr/account/{id}")
     * @Method({"PUT"})
     */
    public function editAccountAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $account = $this->findEntityBy('Odr\Account', $id, 'Account not found'); /* @var $account EntityDir\Odr\Account*/
        $this->denyAccessIfOdrDoesNotBelongToUser($account->getOdr());

        $data = $this->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());

        $this->getEntityManager()->flush();

        return $account;
    }

    /**
     * @Route("/odr/account/{id}")
     * @Method({"DELETE"})
     */
    public function accountDelete($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $account = $this->findEntityBy('Odr\Account', $id, 'Account not found'); /* @var $account EntityDir\Odr\Account */
        $this->denyAccessIfOdrDoesNotBelongToUser($account->getOdr());

        $this->getEntityManager()->remove($account);

        $this->getEntityManager()->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Odr\Account $account, array $data)
    {
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

        if (array_key_exists('balance_on_court_order_date', $data)) {
            $account->setBalanceOnCourtOrderDate($data['balance_on_court_order_date']);
        }
    }
}
