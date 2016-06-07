<?php

namespace AppBundle\Controller;

use AppBundle\Exception\NotFound;
use AppBundle\Service\Auth\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

abstract class RestController extends Controller
{
    /**
     * @return array
     */
    protected function deserializeBodyContent(Request $request, array $assertions = [])
    {
        if ($this->container->has('kernel.listener.responseConverter')) {
            $return = $this->container->get('kernel.listener.responseConverter')->requestContentToArray($request);

            $this->validateArray($return, $assertions);

            return $return;
        }

        return $request->getContent();
    }

    /**
     * @param array $data
     * @param array $assertions key=>rule
     * 
     * @throws \InvalidArgumentException
     */
    protected function validateArray($data, array $assertions = [])
    {
        $errors = [];

        foreach ($assertions as $requiredKey => $validation) {
            switch ($validation) {
                case 'notEmpty':
                    if (empty($data[$requiredKey])) {
                        $errors[] = "Expected value for '$requiredKey' key";
                    }
                    break;

                case 'mustExist':
                    if (!array_key_exists($requiredKey, $data)) {
                        $errors[] = "Missing '$requiredKey' key";
                    }
                    break;

                default:
                    throw new \InvalidArgumentException(__METHOD__.": {$validation} not recognised.");
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException('Errors('.count($errors).'): '.implode(', ', $errors));
        }
    }

    protected function getRepository($entityClass)
    {
        return $this->getDoctrine()->getManager()->getRepository('AppBundle\\Entity\\'.$entityClass);
    }

    /**
     * @param string    $entityClass
     * @param array|int $criteriaOrId
     * @param string    $errorMessage
     * 
     * @throws NotFound
     */
    protected function findEntityBy($entityClass, $criteriaOrId, $errorMessage = null)
    {
        $repo = $this->getRepository($entityClass);
        $entity = is_array($criteriaOrId) ? $repo->findOneBy($criteriaOrId) : $repo->find($criteriaOrId);

        if (!$entity) {
            throw new NotFound($errorMessage ?: $entityClass.' not found');
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
     * Attach setting to REquest as header, to be read by REstInputOuputFormatter kernel listener.
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
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->get('authService');
    }

    /**
     * @param Report $report
     */
    protected function denyAccessIfReportDoesNotBelongToUser(EntityDir\Report $report)
    {
        if (!in_array($this->getUser()->getId(), $report->getClient()->getUserIds())) {
            throw $this->createAccessDeniedException('Report does not belong to user');
        }
    }

    /**
     * @param Client $client
     */
    protected function denyAccessIfClientDoesNotBelongToUser(EntityDir\Client $client)
    {
        if (!in_array($this->getUser()->getId(), $client->getUserIds())) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }
    }

    protected function persistAndFlush($e)
    {
        $this->getEntityManager()->persist($e);
        $this->getEntityManager()->flush($e);
    }
}
