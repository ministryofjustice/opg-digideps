<?php

declare(strict_types=1);

namespace App\Service\RestHandler;

use App\Entity\Organisation;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\Repository\OrganisationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException as OptimisticLockExceptionAlias;
use Doctrine\ORM\ORMException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganisationRestHandler
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly ValidatorInterface $validator,
        private readonly OrganisationRepository $orgRepository,
        private readonly UserRepository $userRepository,
        private readonly OrganisationFactory $organisationFactory,
        private readonly array $sharedEmailDomains
    ) {
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockExceptionAlias
     */
    public function create(array $data): Organisation
    {
        if (!$this->verifyPostedData($data)) {
            throw new OrganisationCreationException(sprintf('Missing key or null value given in request: %s', json_encode($data)));
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
            (bool) $data['is_activated']
        );

        $this->throwExceptionOnInvalidEntity($organisation);

        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    private function verifyPostedData(array $data): bool
    {
        return
            isset($data['name'])
            && isset($data['email_identifier'])
            && isset($data['is_activated']);
    }

    private function orgWithEmailIdExists($emailId): bool
    {
        $org = $this->orgRepository->findOneBy(['emailIdentifier' => $emailId]);

        return $org instanceof Organisation ? true : false;
    }

    private function throwExceptionOnInvalidEntity(Organisation $entity): void
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new OrganisationCreationException(json_encode($errors));
        }
    }

    /**
     * @return bool
     *
     * @throws OptimisticLockExceptionAlias
     * @throws \Exception
     * @throws ORMException
     */
    public function delete(int $id)
    {
        if (null === ($organisation = $this->orgRepository->find($id))) {
            throw new \Exception();
        }

        if ($this->orgRepository->hasActiveEntities($id)) {
            throw new \Exception();
        }

        $organisation->setDeletedAt(new \DateTime());
        $this->em->flush($organisation);

        return true;
    }

    /**
     * /**
     * @throws OptimisticLockExceptionAlias
     * @throws ORMException
     */
    public function update(array $data, int $id): ?Organisation
    {
        if (!$this->verifyPostedData($data)) {
            throw new OrganisationUpdateException(sprintf('Missing key or null value given in request: %s', json_encode($data)));
        }

        if (null === ($organisation = $this->orgRepository->find($id))) {
            return null;
        }

        if ($data['email_identifier'] !== $organisation->getEmailIdentifier() && $this->orgWithEmailIdExists($data['email_identifier'])) {
            throw new OrganisationUpdateException('Email identifer already in use');
        }

        $this->populateOrganisation($data, $organisation);
        $this->throwExceptionOnInvalidEntity($organisation);

        $this->em->persist($organisation);
        $this->em->flush();

        return $organisation;
    }

    private function populateOrganisation(array $data, Organisation $organisation): void
    {
        $organisation
            ->setName($data['name'])
            ->setEmailIdentifier($data['email_identifier'])
            ->setIsActivated((bool) $data['is_activated']);
    }

    /**
     * @throws OptimisticLockExceptionAlias
     * @throws ORMException
     */
    public function addUser(int $orgId, int $userId): void
    {
        $this
            ->attemptGetOrganisation($orgId)
            ->addUser($this->attemptGetUser($userId));

        $this->em->flush();
    }

    private function attemptGetOrganisation(int $orgId): ?Organisation
    {
        if (null === ($organisation = $this->orgRepository->find($orgId))) {
            throw new \InvalidArgumentException('Invalid organisation id');
        }

        return $organisation;
    }

    private function attemptGetUser(int $userId): ?User
    {
        if (null === ($user = $this->userRepository->find($userId))) {
            throw new \InvalidArgumentException('Invalid user id');
        }

        return $user;
    }

    /**
     * @throws OptimisticLockExceptionAlias
     * @throws ORMException
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
}
