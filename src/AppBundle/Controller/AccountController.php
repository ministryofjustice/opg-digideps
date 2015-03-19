<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;


class AccountController extends RestController
{
    
    /**
     * @Route("/report/get-accounts/{id}")
     * @Method({"GET"})
     */
    public function getAccountsAction($id)
    {
        $report = $this->findEntityBy('Report', $id);
        
        $accounts = $this->getRepository('Account')->findByReport($report);
       
        if(count($accounts) == 0){
            return [];
        }
        return $accounts;
    }
    
    /**
     * @Route("/report/add-account")
     * @Method({"POST"})
     */
    public function addAccountAction()
    {
        $accountData = $this->deserializeBodyContent();
   
        $report = $this->findEntityBy('Report', $accountData['report']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$accountData['report']." does not exists");
        }
        
        $account = new EntityDir\Account();
        $account->setBank($accountData['bank']);
        $account->setSortCode($accountData['sort_code']);
        $account->setAccountNumber($accountData['account_number']);
        $account->setOpeningDate(new \DateTime($accountData['opening_date']));
        $account->setOpeningBalance($accountData['opening_balance']);
        $account->setReport($report);
        $account->setLastEdit(new \DateTime());
        
        // add empty transactions. one of each type.
        foreach ($this->getRepository('AccountTransactionType')->findAll() as $transactionType) {
            $transaction = new EntityDir\AccountTransaction($account, $transactionType, 0.0);
            $account->getTransactions()->add($transaction); 
        }
        
        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();
        
        return [ 'id' => $account->getId() ];
    }
    
   /**
     * @Route("/report/find-account-by-id/{id}/{serialiseGroup}", defaults={"serialiseGroup": null})
     * @Method({"GET"})
     */
    public function get($id, $serialiseGroup = null)
    {
        $this->setJmsSerialiserGroup($serialiseGroup);
        
        $ret = $this->findEntityBy('Account', $id, 'Account not found');

        return $ret;
    }
    
}