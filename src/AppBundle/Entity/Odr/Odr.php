<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Client;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Odr\OdrRepository")
 * @ORM\Table(name="odr")
 */
class Odr
{
    const PROPERTY_AND_AFFAIRS = 2;

    /**
     * @var int
     *
     * @JMS\Groups({"odr", "odr_id"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Client
     *
     * @JMS\Groups({"client"})
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Client", inversedBy="odr")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

    /**
     * @JMS\Groups({"odr"})
     * @JMS\Type("AppBundle\Entity\Odr\VisitsCare")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Odr\VisitsCare", mappedBy="odr", cascade={"persist"})
     **/
    private $visitsCare;

    /**
     * @JMS\Groups({"odr-account"})
     * @JMS\Type("array<AppBundle\Entity\Odr\Account>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Odr\Account", mappedBy="odr", cascade={"persist"})
     */
    private $bankAccounts;

    /**
     * @JMS\Groups({"debts"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Odr\Debt", mappedBy="odr", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $debts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debts"})
     *
     * @ORM\Column(name="has_debts", type="string", length=5, nullable=true)
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @var bool
     *
     * @JMS\Groups({"odr"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"odr"})
     * @JMS\Accessor(getter="getSubmitDate")
     * @JMS\Type("DateTime")
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;

    /**
     * Odr constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->bankAccounts = new ArrayCollection();
        $this->debts = new ArrayCollection();
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param int $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getVisitsCare()
    {
        return $this->visitsCare;
    }

    /**
     * @param mixed $visitsCare
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;
    }

    /**
     * @return bool
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * @param bool $submitted
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;
    }

    /**
     * @return \DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @param \DateTime $submitDate
     */
    public function setSubmitDate($submitDate)
    {
        $this->submitDate = $submitDate;
    }

    /**
     * @return mixed
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }

    /**
     * @param mixed $bankAccounts
     */
    public function setBankAccounts($bankAccounts)
    {
        $this->bankAccounts = $bankAccounts;
    }

    /**
     * @return mixed
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @param mixed $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;
    }

    /**
     * @return mixed
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param mixed $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;
    }

    /**
     * @param Debt $debt
     */
    public function addDebt(Debt $debt)
    {
        if (!$this->debts->contains($debt)) {
            $this->debts->add($debt);
        }

        return $this;
    }

    /**
     * @param string $typeId
     *
     * @return Debt
     */
    public function getDebtByTypeId($typeId)
    {
        return $this->getDebts()->filter(function (Debt $debt) use ($typeId) {
            return $debt->getDebtTypeId() == $typeId;
        })->first();
    }

    /**
     * Get assets total value.
     *
     * @JMS\VirtualProperty
     * @JMS\Type("string")
     * @JMS\SerializedName("debts_total_amount")
     * @JMS\Groups({"debts"})
     *
     * @return float
     */
    public function getDebtsTotalAmount()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }
}
