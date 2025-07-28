<?php

namespace App\Controller\Ndr;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AccountController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/ndr/{ndrId}/account', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function addAccount(Request $request, int $ndrId): array
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

    #[Route(path: '/ndr/account/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): EntityDir\Ndr\BankAccount
    {
        if ($request->query->has('groups')) {
            $this->formatter->setJmsSerialiserGroups($request->query->all('groups'));
        }

        $account = $this->findEntityBy(EntityDir\Ndr\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfNdrDoesNotBelongToUser($account->getNdr());

        $this->formatter->setJmsSerialiserGroups(['ndr-account', 'bank-acccount-ndr', 'ndr_id']);

        return $account;
    }

    #[Route(path: '/ndr/account/{id}', methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function editAccount(Request $request, int $id): int
    {
        $account = $this->findEntityBy(EntityDir\Ndr\BankAccount::class, $id, 'Account not found');
        $this->denyAccessIfNdrDoesNotBelongToUser($account->getNdr());

        $data = $this->formatter->deserializeBodyContent($request);

        $this->fillAccountData($account, $data);

        $this->em->flush();

        return $account->getId();
    }

    #[Route(path: '/ndr/account/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function accountDelete($id): array
    {
        $account = $this->findEntityBy(EntityDir\Ndr\BankAccount::class, $id, 'Account not found'); /* @var $account EntityDir\Ndr\BankAccount */
        $this->denyAccessIfNdrDoesNotBelongToUser($account->getNdr());

        $this->em->remove($account);
        $this->em->flush();

        return [];
    }

    private function fillAccountData(EntityDir\Ndr\BankAccount $account, array $data): void
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
