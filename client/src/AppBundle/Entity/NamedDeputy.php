<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
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
    use AddressTrait;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     */
    private $deputyNo;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     *
     */
    private $firstname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     */
    private $lastname;

    /**
     * @var string
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @JMS\Type("string")
     *
     */
    private $email1;

    /**
     * @var string
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @JMS\Type("string")
     *
     */
    private $email2;

    /**
     * @var string
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     * @JMS\Type("string")
     *
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report-submitted-by", "named-deputy"})
     */
    private $depAddrNo;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({ "report-submitted-by", "named-deputy"})
     */
    private $address4;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({ "report-submitted-by", "named-deputy"})
     */
    private $address5;

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
