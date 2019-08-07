<?php

namespace AppBundle\Service\RestHandler;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\OrganisationRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException as OptimisticLockExceptionAlias;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganisationRestHandler
{
    /** @var EntityManager */
    private $em;

    /** @var ValidatorInterface */
    private $validator;

    /** @var OrganisationRepository */
    private $repository;

    /**
     * @param EntityManager $em
     * @param ValidatorInterface $validator
     * @param OrganisationRepository $repository
     */
    public function __construct(EntityManager $em, ValidatorInterface $validator, OrganisationRepository $repository)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->repository = $repository;
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

        if ($this->orgWithEmailIdExists($data['email_identifier'])) {
            throw new OrganisationCreationException('Email identifer already exists');
        }

        $organisation = new Organisation();
        $this->populateOrganisation($data, $organisation);
        $this->throwExceptionOnInvalidEntity($organisation);

        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    /**
     * @param array $data
     * @param int $id
     * @return Organisation|null
     * @throws OptimisticLockExceptionAlias
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(array $data, int $id): ?Organisation
    {
        if (!$this->verifyPostedData($data)) {
            throw new OrganisationUpdateException(sprintf(
                'Missing key or null value given in request: %s',
                json_encode($data)
            ));
        }

        if (null === ($organisation = $this->repository->find($id))) {
            return null;
        }

        if ($data['email_identifier'] !== $organisation->getEmailIdentifier() && $this->orgWithEmailIdExists($data['email_identifier'])) {
            throw new OrganisationCreationException('Email identifer already exists');
        }

        $this->populateOrganisation($data, $organisation);
        $this->throwExceptionOnInvalidEntity($organisation);

        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function verifyPostedData(array $data): bool
    {
        return
            isset($data['name'])
            && isset($data['email_identifier'])
            && isset($data['is_activated']);
    }

    /**
     * @param $emailId
     * @return bool
     */
    private function orgWithEmailIdExists($emailId): bool
    {
        $org = $this->repository->findOneBy(['emailIdentifier' => $emailId]);

        return $org instanceof Organisation ? true : false;
    }

    /**
     * @param array $data
     * @param Organisation $organisation
     */
    private function populateOrganisation(array $data, Organisation $organisation): void
    {
        $organisation
            ->setName($data['name'])
            ->setEmailIdentifier($data['email_identifier'])
            ->setIsActivated((bool)$data['is_activated']);
    }

    /** @param Organisation $entity */
    private function throwExceptionOnInvalidEntity(Organisation $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new OrganisationCreationException((string)$errors);
        }
    }
}
