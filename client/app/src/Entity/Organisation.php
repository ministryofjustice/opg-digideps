<?php

namespace OPG\Digideps\Frontend\Entity;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organisation.
 */
class Organisation
{
    /**
     * @var int
     */
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'organisation.name.notBlank')]
    #[Assert\Length(max: 256, maxMessage: 'organisation.name.maxLength')]
    private $name;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $emailIdentifier;

    /**
     * @var bool
     */
    #[JMS\Type('boolean')]
    #[Assert\NotNull(message: 'organisation.isActivated.notBlank')]
    private $isActivated;

    /**
     * @var array<User>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Frontend\Entity\User>')]
    private array $users = [];

    /**
     * @var array<Client>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Frontend\Entity\Client>')]
    private array $clients = [];

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['total-user-count'])]
    private $totalUserCount;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['total-client-count'])]
    private $totalClientCount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailIdentifier()
    {
        return $this->emailIdentifier;
    }

    public function getEmailIdentifierDisplay()
    {
        if ($this->getIsDomainIdentifier()) {
            return '*@' . $this->emailIdentifier;
        } else {
            return $this->emailIdentifier;
        }
    }

    /**
     * @param string $emailIdentifier
     */
    public function setEmailIdentifier($emailIdentifier): static
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDomainIdentifier()
    {
        return strpos($this->emailIdentifier, '@') === false;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->getIsDomainIdentifier() ? '' : $this->emailIdentifier;
    }

    /**
     * @param string $emailIdentifier
     */
    public function setEmailAddress($emailIdentifier): static
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailDomain()
    {
        return $this->getIsDomainIdentifier() ? $this->emailIdentifier : '';
    }

    /**
     * @param string $emailIdentifier
     */
    public function setEmailDomain($emailIdentifier): static
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function isActivated()
    {
        return $this->isActivated;
    }

    /**
     * @param string $isActivated
     */
    public function setIsActivated($isActivated): static
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    public function hasUser(User $user): bool
    {
        return array_any($this->users->toArray() ?: [], fn ($currentUser) =>
            $user->getId()
            && $currentUser instanceof User
            && $currentUser->getId()
            && $user->getId() == $currentUser->getId());
    }

    public function getUserById(int $userId): ?User
    {
        return array_find($this->users->toArray(), fn ($user) => $user->getId() === $userId);

    }

    /**
     * @param User[] $users
     */
    public function setUsers(array $users): static
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user): static
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return Client[]
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param Client[] $clients
     */
    public function setClients($clients): static
    {
        $this->clients = $clients;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalUserCount()
    {
        return $this->totalUserCount;
    }

    /**
     * @param int $count
     */
    public function setTotalUserCount($count): static
    {
        $this->totalUserCount = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalClientCount()
    {
        return $this->totalClientCount;
    }

    /**
     * @param int $count
     */
    public function setTotalClientCount($count): static
    {
        $this->totalClientCount = $count;

        return $this;
    }
}
