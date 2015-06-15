<?php
namespace AppBundle\Service;

use AppBundle\Entity\User;

class UserQueryFilter
{
    const ENTITY_ACCOUNT = 'AppBundle\Entity\Account';
    const ENTITY_ACCOUNT_TRANSACTION = 'AppBundle\Entity\AccountTransaction';
    const ENTITY_ASSET = 'AppBundle\Entity\Asset';
    const ENTITY_CLIENT = 'AppBundle\Entity\Client';
    const ENTITY_CONTACT = 'AppBundle\Entity\Contact';
    const ENTITY_COURT_ORDER_TYPE = 'AppBundle\Entity\CourtOrderType';
    const ENTITY_DECISION = 'AppBundle\Entity\Decision';
    const ENTITY_REPORT = 'AppBundle\Entity\Report';
    
    /**
     * @var currently logged in user
     */
    private $user;
    
    /**
     * @param User $user
     * @return \AppBundle\Service\UserQueryFilter
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }
    
    /**
     * @param type $qb
     * @param type $targetEntity
     * @return type
     * @throws \Exception
     */
    public function filterByUser($qb, $targetEntity)
    {
        switch($targetEntity){
            case self::ENTITY_ACCOUNT:
                $qb = $this->accountFilter($qb);
                break; 
            case self::ENTITY_ACCOUNT_TRANSACTION:
                $qb = $this->accountTransactionFilter($qb);
                break;
            case self::ENTITY_ASSET:
                $qb = $this->assetFilter($qb);
                break;
            case self::ENTITY_CLIENT:
                $qb = $this->clientFilter($qb);
                break;
            case self::ENTITY_CONTACT:
                $qb = $this->contactFilter($qb);
                break;
            case self::ENTITY_COURT_ORDER_TYPE:
                $qb = $this->courtOrderTypeFilter($qb);
                break;
            case self::ENTITY_DECISION:
                $qb = $this->decisionFilter($qb);
                break;
            case self::ENTITY_REPORT:
                $qb = $this->reportFilter($qb);
                break;
        }
        return $qb;
    }
    
    private function accountFilter($qb)
    {
        //logic
    }
    
    private function accountTransactionFilter($qb)
    {
        //logic
    }
    
    private function assetFilter($qb)
    {
        //logic
    }
    
    private function clientFilter($qb)
    {
        //logic
    }
    
    private function contactFilter($qb)
    {
        //logic
    }
    
    private function courtOrderTypeFilter($qb)
    {
        //logic
    }
    
    private function decisionFilter($qb)
    {
        //logic
    }
    
    private function reportFilter($qb)
    {
        //logic
    }
}