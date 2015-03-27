<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class AccountRepository extends \Doctrine\ORM\EntityRepository
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
