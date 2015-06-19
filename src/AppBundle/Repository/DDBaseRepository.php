<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AppBundle\Service\UserQueryFilter;
use Doctrine\DBAL\LockMode;

class DDBaseRepository extends EntityRepository
{
    private $queryFilter;
    
    /**
     * @param UserQueryFilter $queryFilter
     * @return \AppBundle\Repository\DDBaseRepository
     */
    public function setQueryFilter(UserQueryFilter $queryFilter)
    {
        $this->queryFilter = $queryFilter;
        return $this;
    }
    
    /**
     * 
     * @param type $qb
     * @return type
     */
    public function filterByUser($qb)
    {
        $qbFinal = $this->queryFilter->filterByUser($qb,$this->getClassName());
        return $qbFinal;
    }
    
    /**
     * @param type $id
     * @param type $lockMode
     * @param type $lockVersion
     * @return type
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null) {
        if(!empty($this->queryFilter)){
            $qb = $this->createQueryBuilder('q');
            $qb->where('q.id = :id')->setParameter('id',$id);
            return $this->filterByUser($qb)->getQuery()->getOneOrNullResult();
        }
        return parent::find($id, $lockMode, $lockVersion);
    }
    
    /**
     * @return type
     */
    public function findAll() 
    {
        if(!empty($this->queryFilter)){
            return $this->findBy([]);
        }
        return parent::findAll();
    }
    
    /**
     * @param array $criteria
     * @param array $orderBy
     * @return type
     */
    public function findOneBy(array $criteria, array $orderBy = null) 
    {  
        if(!empty($this->queryFilter)){
            $qb = $this->createQueryBuilder('q');
            
            //apply filter criteria
            if(!empty($criteria)){
                foreach($criteria as $field => $value){
                    $qb->andWhere('q.'.$field.' = :'.$field)->setParameter($field, $value);
                }
            }
            
            $qbFiltered = $this->filterByUser($qb);
            
            //apply order by
            if(!empty($orderBy)){
                if(is_array($orderBy)){
                    foreach($orderBy as $key => $value){
                        $qbFiltered->addOrderBy($key, $value);
                    }
                }else {
                    $qbFiltered->addOrderBy($orderBy);
                }
            }
            return $qbFiltered->getQuery()->getOneOrNullResult();
        }
        return parent::findOneBy($criteria, $orderBy);
    }
    
    
    /**
     * @param array $criteria
     * @param array $orderBy
     * @param type $limit
     * @param type $offset
     * @return type
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) 
    {
        if(!empty($this->queryFilter)){
            $qb = $this->createQueryBuilder('q');
            
            //apply filter criteria
            if(!empty($criteria)){
                foreach($criteria as $field => $value){
                    $qb->andWhere('q.'.$field.' = :'.$field)->setParameter($field, $value);
                }
            }
            
            $qbFiltered = $this->filterByUser($qb);
            
            //apply order by
            if(!empty($orderBy)){
                if(is_array($orderBy)){
                    foreach($orderBy as $key => $value){
                        $qbFiltered->addOrderBy($key, $value);
                    }
                }else {
                    $qbFiltered->addOrderBy($orderBy);
                }
            }
            
            //apply limit
            if(!empty($limit)){
                $qbFiltered->setMaxResults($limit);
            }
            
            //apply offset
            if(!empty($ffset)){
                $qbFiltered->setFirstResult($offset);
            }
            return $qbFiltered->getQuery()->execute();
        }
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }
}