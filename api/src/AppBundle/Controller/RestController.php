<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\EventListener\RestInputOuputFormatter;
use AppBundle\Exception\NotFound;
use AppBundle\Service\Auth\AuthService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

abstract class RestController extends Controller
{
    /**
     * @param Request $request
     * @param array $assertions
     * @param RestInputOuputFormatter $formatter
     * @return array
     */
    protected function deserializeBodyContent(Request $request, array $assertions = [], RestInputOuputFormatter $formatter)
    {
        $return = $formatter->requestContentToArray($request);

        $this->validateArray($return, $assertions);

        return $return;
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
                    throw new \InvalidArgumentException(__METHOD__ . ": {$validation} not recognised.");
            }
        }

        if (!empty($errors)) {
            throw new \InvalidArgumentException('Errors(' . count($errors) . '): ' . implode(', ', $errors));
        }
    }

    /**
     * @param $entityClass string
     *
     * @return EntityRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->getDoctrine()->getManager()->getRepository($entityClass);
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
            throw new NotFound($errorMessage ?: $entityClass . ' not found');
        }

        return $entity;
    }

    /**
     * @param EntityManagerInterface $em
     * @return EntityManagerInterface
     */
    protected function getEntityManager(EntityManagerInterface $em)
    {
        return $em;
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
    protected function setJmsSerialiserGroups(array $groups, RestInputOuputFormatter $formatter)
    {
        $formatter->addContextModifier(function ($context) use ($groups) {
            $context->setGroups($groups);
        });
    }

    /**
     * @return AuthService
     */
    protected function getAuthService(AuthService $authService)
    {
        return $authService;
    }

    /**
     * @param EntityDir\ReportInterface $report
     */
    protected function denyAccessIfReportDoesNotBelongToUser(EntityDir\ReportInterface $report)
    {
        if (!$this->isGranted('edit', $report->getClient())) {
            throw $this->createAccessDeniedException('Report does not belong to user');
        }
    }

    /**
     * @param EntityDir\Ndr\Ndr $ndr
     */
    protected function denyAccessIfNdrDoesNotBelongToUser(EntityDir\Ndr\Ndr $ndr)
    {
        if (!$this->isGranted('edit', $ndr->getClient())) {
            throw $this->createAccessDeniedException('Ndr does not belong to user');
        }
    }

    /**
     * @param Client $client
     */
    protected function denyAccessIfClientDoesNotBelongToUser(EntityDir\Client $client)
    {
        if (!$this->isGranted('edit', $client)) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }
    }

    protected function persistAndFlush($e)
    {
        $this->getEntityManager()->persist($e);
        $this->getEntityManager()->flush($e);
    }
}
