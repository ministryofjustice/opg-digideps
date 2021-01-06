<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\AddressTrait;
use AppBundle\Entity\Traits\CreationAudit;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="client_contact",
 *     indexes={
 *     @ORM\Index(name="ix_clientcontact_client_id", columns={"client_id"}),
 *     @ORM\Index(name="ix_clientcontact_created_by", columns={"created_by"})
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\ClientContactRepository")
 *
 */
class ClientContact
{
    use CreationAudit;
    use AddressTrait;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"clientcontact"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="user_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"clientcontact"})
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"clientcontact"})
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"clientcontact"})
     *
     * @ORM\Column(name="job_title", type="string", length=150, nullable=true)
     */
    private $jobTitle;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"clientcontact"})
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"clientcontact"})
     *
     * The following is changed to unique=false, as the migration was missing,
     * and prod data contains duplicate, making it impossible to add the
     * migration now, unless the data is cleaned
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=true, unique=false)
     */
    private $email;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"clientcontact"})
     *
     * @ORM\Column(name="org_name", type="string", length=150, nullable=true)
     */
    private $orgName;

    /**
     * @var Client
     *
     * @JMS\Type("AppBundle\Entity\Client")
     * @JMS\Groups({"clientcontact-client"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="clientContacts")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     *
     * @return $this
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrgName()
    {
        return $this->orgName;
    }

    /**
     * @param string $orgName
     *
     * @return $this
     */
    public function setOrgName($orgName)
    {
        $this->orgName = $orgName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
}
