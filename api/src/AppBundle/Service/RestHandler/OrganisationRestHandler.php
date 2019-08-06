<?php

namespace AppBundle\Service\RestHandler;

use AppBundle\Entity\Organisation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException as OptimisticLockExceptionAlias;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganisationRestHandler
{
    /** @var EntityManager */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param EntityManager $em
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManager $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * @param array $data
     * @return Organisation
     * @throws \Doctrine\ORM\ORMException
     * @throws OptimisticLockExceptionAlias
     */
    public function create(array $data): Organisation
    {
        if (!$this->verifyPostedData($data)) {
            throw new OrganisationCreationException(sprintf(
                'Missing key or null value given in request: %s',
                json_encode($data)
            ));
        }

        $organisation = (new Organisation())
            ->setName($data['name'])
            ->setEmailIdentifier($data['email_identifier'])
            ->setIsActivated((bool)$data['is_activated']);

        $this->throwExceptionOnInvalidEntity($organisation);

        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function verifyPostedData(array $data)
    {
        return
            isset($data['name'])
            && isset($data['email_identifier'])
            && isset($data['is_activated']);
    }

    /** @param Organisation $entity */
    private function throwExceptionOnInvalidEntity(Organisation $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new OrganisationCreationException((string) $errors);
        }
    }
}
