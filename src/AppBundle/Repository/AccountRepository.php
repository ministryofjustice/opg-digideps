<?php
namespace AppBundle\Repository;

use AppBundle\Entity\Account;
use AppBundle\Entity\AccountTransaction;

class AccountRepository extends DDBaseRepository
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
