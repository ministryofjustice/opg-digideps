<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Users.
 *
 * @ORM\Table(name="named_deputy", indexes={@ORM\Index(name="named_deputy_no_idx", columns={"deputy_no"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\NamedDeputyRepository")
 */
class NamedDeputy
{
    /**
     * @var int
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="named_deputy_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"named-deputy"})
     * @ORM\Column(name="deputy_no", type="string", length=15, nullable=false)
     */
    private $deputyNo;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({ "named-deputy"})
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     * @JMS\Type("string")
     * @JMS\Groups({ "named-deputy"})
     */
    private $lastname;

    /**
     * @var string
     * @JMS\Groups({"named-deputy"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email1", type="string", length=60, nullable=false, unique=false)
     */
    private $email1;

    /**
     * @var string
     * @JMS\Groups({"named-deputy"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email2", type="string", length=60, nullable=true, unique=false)
     */
    private $email2;

    /**
     * @var string
     * @JMS\Groups({"named-deputy"})
     * @JMS\Type("string")
     *
     * @ORM\Column(name="email3", type="string", length=60, nullable=true, unique=false)
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"named-deputy"})
     * @ORM\Column(name="dep_addr_no", type="integer", length=100, nullable=true)
     */
    private $depAddrNo;

    /**
     * NamedDeputy constructor.
     * @param array $csvRow
     */
    public function __construct(array $csvRow)
    {
        $this->setDeputyNo($csvRow['Deputy No']);
        $this->setFirstname($csvRow['Dep Forename']);
        $this->setLastname($csvRow['Dep Surname']);
        $this->setEmail1($csvRow['Email']);
        $this->setEmail2($csvRow['Email2']);
        $this->setEmail3($csvRow['Email3']);
        $this->setDepAddrNo($csvRow['DepAddr No']);
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
     * @return string
     */
    public function getDeputyNo()
    {
        return $this->deputyNo;
    }

    /**
     * @param string $deputyNo
     *
     * @return $this
     */
    public function setDeputyNo($deputyNo)
    {
        $this->deputyNo = User::padDeputyNumber($deputyNo);
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     *
     * @return $this
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     *
     * @return $this
     */
    public function setEmail1($email1)
    {
        $this->email1 = $email1;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     *
     * @return $this
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     *
     * @return $this
     */
    public function setEmail3($email3)
    {
        $this->email3 = $email3;
        return $this;
    }

    /**
     * @return string
     */
    public function getDepAddrNo()
    {
        return $this->depAddrNo;
    }

    /**
     * @param string $depAddrNo
     *
     * @return $this
     */
    public function setDepAddrNo($depAddrNo)
    {
        $this->depAddrNo = User::padDeputyNumber($depAddrNo);
        return $this;
    }
}
