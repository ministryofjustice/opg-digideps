<?php

namespace AppBundle\Controller;

class AccountControllerTest extends AbstractTestController
{
    /**
     * @test
     */
    public function addAccount()
    {
        $this->login('deputy@example.org');
        
        $client = $this->fixtures->createClient();
        $report = $this->fixtures->createReport($client);
        $this->fixtures->flush();
        
        $return = $this->assertRequest([
            'uri'=>'/report/add-account',
            'method'=>'POST',
            'data' => [
                'report' => $report->getId(),
                'bank' => 'hsbc',
                'sort_code' => '123456',
                'account_number' => '1234',
                'opening_date' => '01/01/2015',
                'opening_balance' => '500'
            ],
            'mustSucceed'=>true
        ]);
        $this->assertTrue($return['data']['id'] > 0);
        
        // assert account created with transactions
        $account = $this->fixtures->getRepo('Account')->find($return['data']['id']); /* @var $account \AppBundle\Entity\Account */
        $transactionTypesTotal = count($this->fixtures->getRepo('AccountTransactionType')->findAll());
        $this->assertCount($transactionTypesTotal, $account->getTransactions(), "transactions not created");

        $this->assertNull($account->getLastEdit(), 'account.lastEdit must be null on creation');
        
        return $account->getId();
    }
    
}
