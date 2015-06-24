<?php
namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\QueryBuilder;

class UserQueryFilter
{
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
    public function filterByUser(QueryBuilder $qb, $targetEntity)
    {
        $interfacesImplemented = class_implements($targetEntity);
        
        if(!in_array("AppBundle\Filter\UserFilterInterface", $interfacesImplemented)){
            throw new \Exception($targetEntity." must implement UserFilterInterface to appy user filter");
        }        
        return $targetEntity::applyUserFilter($qb, $this->user->getId());
    }
}