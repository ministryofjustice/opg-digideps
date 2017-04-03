<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Team
 *
 * @codeCoverageIgnore
 */
class Team
{
    const MAX_PA_ADMINS = 3;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"user_details_full", "user_details_basic", "admin_add_user"})
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Groups({"user"})
     *
     * @JMS\Type("ArrayCollection<AppBundle\Entity\User>")
     */
    private $members;

    /**
     * @var string
     *
     * @JMS\Groups({"team"})
     * @JMS\Type("string")
     */
    private $teamName;

    /**
     * Team constructor.
     * @param string $teamName
     */
    public function __construct($teamName)
    {
        $this->teamName = $teamName;
        $this->members = new ArrayCollection();
    }

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
     * @return mixed
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param mixed $members
     *
     * @return $this
     */
    public function setMembers($members)
    {
        $this->members = $members;
        return $this;
    }

    /**
     * Add a member
     *
     * @param User $user
     * @return $this
     */
    public function addMember(User $user)
    {
        if (!$this->members->contains($user)) {
            $this->members->add($user);
        }

        return $this;
    }

    /**
     * Add Members
     *
     * @param ArrayCollection $members Collection being added
     *
     * @return $this
     */
    public function addMembers(ArrayCollection $members)
    {
        $this->members = new ArrayCollection(
            array_merge(
                $this->members->toArray(),
                $members->toArray()
            )
        );

        return $this;
    }

    /**
     * Remove a member from the collection
     *
     * @param mixed $member collection being removed
     *
     * @return $this
     */
    public function removeMember($member)
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @param string $teamName
     *
     * @return $this
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;
        return $this;
    }

    public function canAddAdmin(User $targetUser = null)
    {
        // don't show role for named deputy
        if (!empty($targetUser) && $targetUser->isNamedDeputy()) {
            return false;
        }

        $adminCount = 0;
        /** @var User $member */
        foreach ($this->members as $member)
        {
            if ($member->isPaAdministrator()) {
                $adminCount++;
            }
        }

        return $adminCount < self::MAX_PA_ADMINS;
    }
}
