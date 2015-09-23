<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Exception\NotFound;
use AppBundle\EventListener\RestInputOuputFormatter;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;

abstract class RestController extends Controller
{
    /**
     * @return array
     */
    protected function deserializeBodyContent(array $assertions = [])
    {
        if ($this->container->has('kernel.listener.responseConverter')) {
            $return = $this->container->get('kernel.listener.responseConverter')->requestContentToArray($this->getRequest());

            $this->validateArray($return, $assertions);

            return $return;
        }

        return $this->getRequest()->getContent();
    }

    /**
     * @param array $data
     * @param array $assertions key=>rule
     * 
     * @throws \InvalidArgumentException
     */
    private function validateArray($data, array $assertions = [])
    {
        foreach ($assertions as $requiredKey => $validation) {
            switch ($validation) {
                case 'notEmpty':
                    if (empty($data[$requiredKey])) {
                        throw new \InvalidArgumentException("Expected value for '$requiredKey' key");
                    }
                    break;
                    
                case 'mustExist':
                    if (!array_key_exists($requiredKey, $data)) {
                        throw new \InvalidArgumentException("Missing '$requiredKey' key");
                    }
                    break;
            }
        }
    }


    protected function getRepository($entityClass)
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle\\Entity\\' . $entityClass);
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
        $entity = is_array($criteriaOrId) ? $repo->findOneBy($criteriaOrId) : $repo->find($criteriaOrId);
        
        if (!$entity) {
            throw new AppExceptions\NotFound($errorMessage ? : $entityClass . ' not found');
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
        foreach ($keySetters as $k => $setter) {
            if (array_key_exists($k, $data)) {
                $object->$setter($data[$k]);
            }
        }
    }


    /**
     * Set serialise group used by JMS serialiser to composer ouput response
     * Attach setting to REquest as header, to be read by REstInputOuputFormatter kernel listener
     * 
     * @param string $groups user 
     */
    protected function setJmsSerialiserGroups(array $groups)
    {
        $this->get('kernel.listener.responseConverter')->addContextModifier(function ($context) use ($groups) {
            $context->setGroups($groups);
        });
    }

    /**
     * @return \AppBundle\Service\Mailer\MailFactory
     */
    protected function getMailFactory()
    {
        return $this->get('mailFactory');
    }


    /**
     * @return \AppBundle\Service\Mailer\MailSender
     */
    protected function getMailSender()
    {
        return $this->get('mailSender');
    }

}