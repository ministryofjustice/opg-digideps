<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 *
 * @ORM\Table(name="account_transaction")
 * @ORM\Entity
 */
class AccountTransaction
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;
    
    /**
     * @var Account
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account", inversedBy="transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;
    
    /**
     * @var AccountTransactionType
     * 
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AccountTransactionType")
     * @ORM\JoinColumn(name="account_transaction_type_id", referencedColumnName="id")
     */
    private $transactionType;
    
    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=14, scale=2, nullable=false)
     */
    private $amount;
    
    private $moreDetails;
    
    private $hasMoreDetails;
    
    public function __construct(Account $account, AccountTransactionType $transactionType, $amount)
    {
        $this->account = $account;
        $this->transactionType = $transactionType;
        $this->amount = $amount;
    }
    
    public function getAccount()
    {
        return $this->account;
    }

    public function getTransactionType()
    {
        return $this->transactionType;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    public function setAccount(Account $account)
    {
        $this->account = $account;
        return $this;
    }

    public function setTransactionType(AccountTransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
        return $this;
    }

    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
        return $this;
    }


    

}
