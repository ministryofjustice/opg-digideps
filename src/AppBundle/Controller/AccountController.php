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
        $account->setBank($accountData['bank'])
            ->setSortCode($accountData['sort_code'])
            ->setAccountNumber($accountData['account_number'])
            ->setOpeningDate(new \DateTime($accountData['opening_date']))
            ->setOpeningBalance($accountData['opening_balance'])
            ->setReport($report)
            ->setLastEdit(new \DateTime());
        
        $this->getRepository('Account')->addEmptyTransactionsToAccount($account);
        
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
        
        $account = $this->findEntityBy('Account', $id, 'Account not found');

        return $account;
    }
    
    /**
     * @Route("/account/{id}")
     * @Method({"PUT"})
     */
    public function edit($id)
    {
        $account = $this->findEntityBy('Account', $id, 'Account not found');
        
        $data = $this->deserializeBodyContent();
        
        // edit transactions
        if (isset($data['money_in']) && isset($data['money_out'])) {
            $transactionRepo = $this->getRepository('AccountTransaction');
            array_map(function($transactionRow) use ($transactionRepo) {
                $transactionRepo->find($transactionRow['id'])
                    ->setAmount($transactionRow['amount'])
                    ->setMoreDetails($transactionRow['more_details']);
            }, array_merge($data['money_in'], $data['money_out']));
            $this->setJmsSerialiserGroup('transactions');
        }
        
        $this->getEntityManager()->flush();
        
        return $account;
    }
    
}