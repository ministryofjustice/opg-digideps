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
     * @JMS\Groups({"client", "clientcontacts"})
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
     * @JMS\Groups({"client", "clientcontacts"})
     *
     * @ORM\Column(name="firstname", type="string", length=100, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"client", "clientcontacts"})
     *
     * @ORM\Column(name="lastname", type="string", length=100, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"client", "clientcontacts"})
     *
     * @ORM\Column(name="job_title", type="string", length=150, nullable=true)
     */
    private $jobTitle;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"client", "clientcontacts"})
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"client", "clientcontacts"})
     *
     * @ORM\Column(name="email", type="string", length=60, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"client", "clientcontacts"})
     *
     * @ORM\Column(name="org_name", type="string", length=150, nullable=true)
     */
    private $orgName;

    /**
     * @var Client
     *
     * @JMS\Type("AppBundle\Entity\Client")
     * @JMS\Groups({"client", "clientcontact-client"})
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="clientContacts")
     */
    private $client;
}