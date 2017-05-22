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
    const MAX_PA_ADMINS = 2;

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
     * @JMS\Type("string")
     *
     * @var string
     */
    private $address1;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $address2;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $address3;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $addressPostcode;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $addressCountry;

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
        // don't show role for named deputy or if logged in user doesn't have permission
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

    public function getAddress1()
    {
        return $this->address1;
    }

    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    public function getAddress3()
    {
        return $this->address3;
    }

    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
    }

    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }
}
