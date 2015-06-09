<?php
namespace AppBundle\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;  
use Doctrine\Common\Annotations\Reader;

class ManyToManyRelationFilter extends SQLFilter
{
    protected $reader;
    
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias) 
    {   
        if (empty($this->reader)) {
            return '';
        }
       
        // The Doctrine filter is called for any query on any entity
        // Check if the current entity is "user aware" (marked with an annotation)
        $manyToManyRelationAware = $this->reader->getClassAnnotation(
            $targetEntity->getReflectionClass(),
            'AppBundle\\Annotation\\ManyToManyRelationAware'
        );
         
        if (!$manyToManyRelationAware) {
            return '';
        }
        
         try {
            // Don't worry, getParameter automatically quotes parameters
            $userId = $this->getParameter('id');
        } catch (\InvalidArgumentException $e) {
            // No user id has been defined
            return '';
        }
        
        $joinTable = $manyToManyRelationAware->joinTable;
        $leftJoinColumn = $manyToManyRelationAware->leftJoinColumn;
        $rightJoinColumn = $manyToManyRelationAware->rightJoinColumn;
        
        if (empty($joinTable) || empty($leftJoinColumn) || empty($rightJoinColumn)) {
            return '';
        }

        $query = sprintf('%s.id = %s.%s and %s.%s = %s', $targetTableAlias, $joinTable, $leftJoinColumn,$joinTable,$rightJoinColumn,$userId);
        
        return $query;
    }
    
    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }
}