<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Exception\NotFound;
use AppBundle\EventListener\RestInputOuputFormatter;

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
        $session = $this->container->get('session');
        $user = $session->has('currentUser')? $session->get('currentUser') : null;
        $entityRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle\\Entity\\' . $entityClass);
        $interfacesImplemented = class_implements($entityRepository->getClassName());
       
        if(!empty($user) && in_array('AppBundle\Filter\UserFilterInterface',$interfacesImplemented) && !$this->container->getParameter('anonymous')){
                $userFilterService = $this->container->get('user.query.filter');
                $userFilterService->setUser($user);
                $entityRepository->setQueryFilter($userFilterService);
        }
        return $entityRepository;
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
    
    
    /**
     * @param mixed $object
     * @param array $data
     * @param array $keySetters
     */
    protected function hydrateEntityWithArrayData($object, array $data, array $keySetters)
    {
        foreach ($keySetters as $k=>$setter) {
            if (array_key_exists($k, $data)) {
                $object->$setter($data[$k]);
            }
        }
    }
    
    
    /**
     * Set serialise group used by JMS serialiser to composer ouput response
     * Attach setting to REquest as header, to be read by REstInputOuputFormatter kernel listener
     * 
     * @param string $group user 
     */
    protected function setJmsSerialiserGroup($group)
    {
        RestInputOuputFormatter::addJmsSerialiserGroupToRequest($this->getRequest(), $group);
    }
    
    /**
     * @return \AppBundle\Service\MailFactory
     */
    protected function getMailFactory()
    {
        return $this->get('mailFactory');
    }
    
    
    /**
     * @return \AppBundle\Service\MailSender
     */
    protected function getMailSender()
    {
        return $this->get('mailSender');
    }
    
    
   
}
