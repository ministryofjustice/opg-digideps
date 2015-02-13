<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Exception\NotFound;

abstract class RestController extends Controller
{
    /**
     * @return array
     */
    protected function deserializeBodyContent()
    {
        if ($this->container->has('kernel.listener.responseConverter')) {
            return $this->container->get('kernel.listener.responseConverter')->requestContentToArray($this->getRequest());
        }
        
        return $this->getRequest()->getContent();
    }
    
    
    protected function getRepository($entityClass)
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle\\Entity\\' . $entityClass);
    }
    
    /**
     * @param string $entityClass
     * @param integer $id
     * @param string $errorMessage
     * 
     * @throws \Exception
     */
    protected function findEntityById($entityClass, $id, $errorMessage = null)
    {
        $entity = $this->getRepository($entityClass)->find((int)$id);
        
        if (!$entity) {
            throw new NotFound($errorMessage ?: $entityClass . ' not found');
        }
        
        return $entity;
    }
    
    /**
     * @param string $entityClass
     * @param array|integer $criteriaOrId
     * @param string $errorMessage
     * 
     * @throws NotFound
     */
    protected function findEntityBy($entityClass, $criteriaOrId, $errorMessage = null)
    {
        $repo = $this->getRepository($entityClass);
        $entity = is_array($criteriaOrId) 
                  ? $repo->findOneBy($criteriaOrId) : $repo->find($criteriaOrId);
        
        if (!$entity) {
            throw new NotFound($errorMessage ?: $entityClass . ' not found');
        }
        
        return $entity;
    }
    
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getManager();
    }
   
}
