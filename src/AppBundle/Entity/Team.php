<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Team
 *
 * @ORM\Table(name="dd_team")
 * @ORM\Entity
 */
class Team
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"audit_log","user"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="team_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Type("ArrayCollection<AppBundle\Entity\User>")
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="teams", cascade={"persist"})
     */
    private $members;

    /**
     * @var string
     *
     * @JMS\Groups({"team"})
     * @JMS\Type("string")
     * @ORM\Column(name="team_name", type="string", length=50, nullable=true)
     */
    private $teamName;

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
    public function setId(int $id)
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
}
