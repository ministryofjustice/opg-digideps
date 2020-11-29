<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends RestController
{
    private EntityManagerInterface $em;
    private RestFormatter $formatter;

    public function __construct(EntityManagerInterface $em, RestFormatter $formatter)
    {
        $this->em = $em;
        $this->formatter = $formatter;
    }

    /**
     * @Route("/ndr/{ndrId}/account", methods={"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAccountAction(Request $request, $ndrId)
    {
        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $data = $this->formatter->deserializeBodyContent($request, [
        ]);

        $account = new EntityDir\Ndr\BankAccount();
        $account->setNdr($ndr);

        $this->fillAccountData($account, $data);

        $this->em->persist($account);
        $this->em->flush();

        return ['id' => $account->getId()];
    }

    /**
     * @Route("/ndr/account/{id}", methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function getOneById(Request $request, $id)
    {
        if ($request->query->has('groups')) {
            $this->formatter->setJmsSerialiserGroups((array) $request->query->get('groups'));
        }

        $account = $this->findEntityBy(EntityDir\Ndr\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfNdrDoesNotBelongToUser($account->getNdr());

        $this->formatter->setJmsSerialiserGroups(['ndr-account', 'bank-acccount-ndr', 'ndr_id']);

        return $account;
    }

    /**
     * @Route("/ndr/account/{id}", methods={"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function editAccountAction(Request $request, $id)
    {
        $account = $this->findEntityBy(EntityDir\Ndr\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Ndr\BankAccount*/
        $this->denyAccessIfNdrDoesNotBelongToUser($account->getNdr());

        $data = $this->formatter->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $account->setLastEdit(new \DateTime());

        $this->em->flush();

        return $account->getId();
    }

    /**
     * @Route("/ndr/account/{id}", methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function accountDelete($id)
    {
        $account = $this->findEntityBy(EntityDir\Ndr\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Ndr\BankAccount */
        $this->denyAccessIfNdrDoesNotBelongToUser($account->getNdr());

        $this->em->remove($account);
        $this->em->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Ndr\BankAccount $account, array $data)
    {
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

        if (array_key_exists('balance_on_court_order_date', $data)) {
            $account->setBalanceOnCourtOrderDate($data['balance_on_court_order_date']);
        }

        if (array_key_exists('is_joint_account', $data)) {
            $account->setIsJointAccount($data['is_joint_account']);
        }
    }
}
