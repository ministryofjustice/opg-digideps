<?php
namespace AppBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Account;
use AppBundle\Entity\AccountTransaction;

class AccountRepository extends EntityRepository
{
    /**
     * @param Account $account
     */
    public function addEmptyTransactionsToAccount(Account $account)
    {
        $transactionTypes = $this->_em->getRepository('AppBundle\Entity\AccountTransactionType')
            ->findBy([], ['displayOrder'=>'ASC']);
        
        foreach ($transactionTypes as $transactionType) {
            $transaction = new AccountTransaction($account, $transactionType, null);
            $account->getTransactions()->add($transaction); 
        }
    }
}
