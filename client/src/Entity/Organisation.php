<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Organisation.
 */
class Organisation
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank(message="organisation.name.notBlank")
     * @Assert\Length(max=256, maxMessage="organisation.name.maxLength")
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $emailIdentifier;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @Assert\NotNull(message="organisation.isActivated.notBlank")
     */
    private $isActivated;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\User>")
     */
    private $users;

    /**
     * @var ArrayCollection
     *
     * @JMS\Type("ArrayCollection<App\Entity\Client>")
     */
    private $clients;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
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
     *
     * @return $this
     */
    public function setName($name)
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
            return '*@'.$this->emailIdentifier;
        } else {
            return $this->emailIdentifier;
        }
    }

    /**
     * @param string $emailIdentifier
     *
     * @return $this
     */
    public function setEmailIdentifier($emailIdentifier)
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDomainIdentifier()
    {
        return false === strpos($this->emailIdentifier, '@');
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
     *
     * @return $this
     */
    public function setEmailAddress($emailIdentifier)
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
     *
     * @return $this
     */
    public function setEmailDomain($emailIdentifier)
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
     *
     * @return $this
     */
    public function setIsActivated($isActivated)
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return bool
     */
    public function hasUser(User $user)
    {
        foreach ($this->users ?: [] as $currentUser) {
            if (
                $user->getId()
                && $currentUser instanceof User && $currentUser->getId()
                && $user->getId() == $currentUser->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return User|null
     */
    public function getUserById(int $userId)
    {
        foreach ($this->users as $user) {
            if ($user->getId() === $userId) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param User[] $users
     *
     * @return $this
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addUser($user)
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
     *
     * @return $this
     */
    public function setClients($clients)
    {
        $this->clients = $clients;

        return $this;
    }
}
