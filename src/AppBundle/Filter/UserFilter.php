<?php
namespace AppBundle\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;  
use Doctrine\Common\Annotations\Reader;

class UserFilter extends SQLFilter
{
    protected $reader;
    
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias) 
    {
        //print_r($targetEntity->getTableName()); die;
        if (empty($this->reader)) {
            return '';
        }

        // The Doctrine filter is called for any query on any entity
        // Check if the current entity is "user aware" (marked with an annotation)
        $userAware = $this->reader->getClassAnnotation(
            $targetEntity->getReflectionClass(),
            'AppBundle\\Annotation\\UserAware'
        );

        if (!$userAware) {
            return '';
        }

        $fieldName = $userAware->userFieldName;

        try {
            // Don't worry, getParameter automatically quotes parameters
            $userId = $this->getParameter('id');
        } catch (\InvalidArgumentException $e) {
            // No user id has been defined
            return '';
        }

        if (empty($fieldName) || empty($userId)) {
            return '';
        }

        $query = sprintf('%s.%s = %s', $targetTableAlias, $fieldName, $userId);

        return $query;
    }
    
    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }
}