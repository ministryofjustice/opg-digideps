<?php

namespace AppBundle\Service\RestHandler;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
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
    private $orgRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var OrganisationFactory */
    private $organisationFactory;

    /** @var array */
    private $sharedEmailDomains;

    /**
     * @param EntityManager $em
     * @param ValidatorInterface $validator
     * @param OrganisationRepository $orgRepository
     * @param UserRepository $userRepository
     * @param OrganisationFactory $organisationFactory
     * @param array $sharedEmailDomains
     */
    public function __construct(
        EntityManager $em,
        ValidatorInterface $validator,
        OrganisationRepository $orgRepository,
        UserRepository $userRepository,
        OrganisationFactory $organisationFactory,
        array $sharedEmailDomains
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->orgRepository = $orgRepository;
        $this->userRepository = $userRepository;
        $this->organisationFactory = $organisationFactory;
        $this->sharedEmailDomains = $sharedEmailDomains;
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

        $data['email_identifier'] = strtolower($data['email_identifier']);

        if ($this->orgWithEmailIdExists($data['email_identifier'])) {
            throw new OrganisationCreationException('Email identifer already in use');
        }

        if (in_array($data['email_identifier'], $this->sharedEmailDomains)) {
            throw new OrganisationCreationException('Cannot set up organisation with specified domain');
        }

        $organisation = $this->organisationFactory->createFromEmailIdentifier(
            $data['name'],
            $data['email_identifier'],
            (bool)$data['is_activated']
        );

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

        if (null === ($organisation = $this->orgRepository->find($id))) {
            return null;
        }

        if ($data['email_identifier'] !== $organisation->getEmailIdentifier() && $this->orgWithEmailIdExists($data['email_identifier'])) {
            throw new OrganisationCreationException('Email identifer already in use');
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
        $org = $this->orgRepository->findOneBy(['emailIdentifier' => $emailId]);

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

    /**
     * @param int $orgId
     * @param int $userId
     * @throws OptimisticLockExceptionAlias
     * @throws \Doctrine\ORM\ORMException
     */
    public function addUser(int $orgId, int $userId): void
    {
        $this
            ->attemptGetOrganisation($orgId)
            ->addUser($this->attemptGetUser($userId));

        $this->em->flush();
    }

    /**
     * @param int $orgId
     * @param int $userId
     * @throws OptimisticLockExceptionAlias
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeUser(int $orgId, int $userId): void
    {
        $organisation = $this->attemptGetOrganisation($orgId);
        $user = $this->attemptGetUser($userId);

        if (!$organisation->getUsers()->contains($user)) {
            throw new \InvalidArgumentException('Cannot remove: User does not belong to organisation');
        }

        $organisation->removeUser($user);
        $this->em->flush();
    }

    /**
     * @param int $orgId
     * @return Organisation|null
     */
    private function attemptGetOrganisation(int $orgId): ?Organisation
    {
        if (null === ($organisation = $this->orgRepository->find($orgId))) {
            throw new \InvalidArgumentException('Invalid organisation id');
        }

        return $organisation;
    }

    /**
     * @param int $userId
     * @return User|null
     */
    private function attemptGetUser(int $userId): ?User
    {
        if (null === ($user = $this->userRepository->find($userId))) {
            throw new \InvalidArgumentException('Invalid user id');
        }

        return $user;
    }
}
