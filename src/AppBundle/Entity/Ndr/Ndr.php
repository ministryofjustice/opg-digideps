<?php

namespace AppBundle\Entity\Ndr;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Traits as NdrTraits;
use AppBundle\Entity\ReportInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Ndr\NdrRepository")
 * @ORM\Table(name="odr")
 */
class Ndr implements ReportInterface
{
    const TYPE_NDR = 'ndr';

    use NdrTraits\IncomeBenefitTrait;
    use NdrTraits\ExpensesTrait;
    use NdrTraits\ActionTrait;
    use NdrTraits\MoreInfoTrait;

    const PROPERTY_AND_AFFAIRS = 2;

    /**
     * @var int
     *
     * @JMS\Groups({"ndr", "ndr_id"})
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Client
     *
     * @JMS\Groups({"ndr-client"})
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Client", inversedBy="ndr")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $client;

    /**
     * @var VisitsCare
     *
     * @JMS\Groups({"ndr"})
     * @JMS\Type("AppBundle\Entity\Ndr\VisitsCare")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Ndr\VisitsCare", mappedBy="ndr", cascade={"persist", "remove"})
     **/
    private $visitsCare;

    /**
     * @var Account[]
     *
     * @JMS\Groups({"ndr-account"})
     * @JMS\Type("array<AppBundle\Entity\Ndr\BankAccount>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ndr\BankAccount", mappedBy="ndr", cascade={"persist", "remove"})
     */
    private $bankAccounts;

    /**
     * @var Debt[]
     *
     * @JMS\Groups({"ndr-debt"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ndr\Debt", mappedBy="ndr", cascade={"persist", "remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $debts;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-debt-management"})
     * @ORM\Column( name="debt_management", type="text", nullable=true)
     */
    private $debtManagement;

    /**
     * @var bool
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-debt"})
     *
     * @ORM\Column(name="has_debts", type="string", length=5, nullable=true)
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @var Asset[]
     *
     * @JMS\Groups({"ndr-asset"})
     * @JMS\Type("array<AppBundle\Entity\Ndr\Asset>")
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Ndr\Asset", mappedBy="ndr", cascade={"persist", "remove"})
     */
    private $assets;

    /**
     * @var bool
     * @JMS\Type("boolean")
     * @JMS\Groups({"ndr"})
     * @ORM\Column(name="no_asset_to_add", type="boolean", options={ "default": false}, nullable=true)
     */
    private $noAssetToAdd;

    /**
     * @var bool
     *
     * @JMS\Groups({"ndr"})
     * @JMS\Type("boolean")
     * @ORM\Column(name="submitted", type="boolean", nullable=true)
     */
    private $submitted;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"ndr"})
     * @JMS\Accessor(getter="getStartDate")
     * @JMS\Type("DateTime")
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"ndr"})
     * @JMS\Accessor(getter="getSubmitDate")
     * @JMS\Type("DateTime")
     * @ORM\Column(name="submit_date", type="datetime", nullable=true)
     */
    private $submitDate;


    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr"})
     * @ORM\Column(name="agreed_behalf_deputy", type="string", length=50, nullable=true)
     */
    private $agreedBehalfDeputy;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"ndr"})
     * @ORM\Column(name="agreed_behalf_deputy_explanation", type="text", nullable=true)
     */
    private $agreedBehalfDeputyExplanation;

    /**
     * Ndr constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->startDate = new \DateTime();
        $this->bankAccounts = new ArrayCollection();
        $this->debts = new ArrayCollection();
        $this->assets = new ArrayCollection();
        $this->stateBenefits = new ArrayCollection();
        $this->oneOff = new ArrayCollection();
        $this->expenses = new ArrayCollection();
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
     * @param Client $client
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
     *
     * @return self
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getDueDate()
    {
        return self::getDueDateBasedOnStartDate($this->getStartDate());
    }

    /**
     * @param $startDate
     * @return \DateTime
     */
    public static function getDueDateBasedOnStartDate(\DateTime $startDate)
    {
        $dueDate = clone $startDate;
        $dueDate->modify('+40 days');

        return $dueDate;
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
     * @return string
     */
    public function getDebtManagement()
    {
        return $this->debtManagement;
    }

    /**
     * @param string $debtManagement
     */
    public function setDebtManagement($debtManagement)
    {
        $this->debtManagement = $debtManagement;
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
     * @JMS\Groups({"ndr-debt"})
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

    /**
     * Add assets.
     *
     * @param Asset $assets
     *
     * @return Ndr
     */
    public function addAsset(Asset $assets)
    {
        $this->assets[] = $assets;

        return $this;
    }

    /**
     * Remove assets.
     *
     * @param Asset $assets
     */
    public function removeAsset(Asset $assets)
    {
        $this->assets->removeElement($assets);
    }

    /**
     * Get assets.
     *
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Set noAssetToAdd.
     *
     * @param bool $noAssetToAdd
     *
     * @return Ndr
     */
    public function setNoAssetToAdd($noAssetToAdd)
    {
        $this->noAssetToAdd = $noAssetToAdd;

        return $this;
    }

    /**
     * Get noAssetToAdd.
     *
     * @return bool
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputy()
    {
        return $this->agreedBehalfDeputy;
    }

    /**
     * @param string $agreedBehalfDeputy
     *
     * @return Ndr
     */
    public function setAgreedBehalfDeputy($agreedBehalfDeputy)
    {
        $this->agreedBehalfDeputy = $agreedBehalfDeputy;
        return $this;
    }

    /**
     * @return string
     */
    public function getAgreedBehalfDeputyExplanation()
    {
        return $this->agreedBehalfDeputyExplanation;
    }

    /**
     * @param string $agreedBehalfDeputyExplanation
     *
     * @return Ndr
     */
    public function setAgreedBehalfDeputyExplanation($agreedBehalfDeputyExplanation)
    {
        $this->agreedBehalfDeputyExplanation = $agreedBehalfDeputyExplanation;
        return $this;
    }

    /**
     * @return decimal
     */
    public function getBalanceOnCourtOrderDateTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $account) {
            $ret += $account->getBalanceOnCourtOrderDate();
        }

        return $ret;
    }

    /**
     * Previous report data. Just return id and type for second api call to allo new JMS groups
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("previous_report_data")
     * @JMS\Groups({"previous-report-data"})
     * @JMS\Type("array")
     *
     * @return array
     */
    public function getPreviousReportData()
    {
        return false;
    }

    /**
     * NDR financial summary, contains bank accounts and balance information
     *
     * @return array
     */
    public function getFinancialSummary() {

        $accounts = [];

        /** @var BankAccount $ba */
        foreach ($this->getBankAccounts() as $ba) {
            $accounts[$ba->getId()]['nameOneLine'] = $ba->getNameOneLine();
            $accounts[$ba->getId()]['bank'] = $ba->getBank();
            $accounts[$ba->getId()]['accountType'] = $ba->getAccountTypeText();
            $accounts[$ba->getId()]['openingBalance'] = $ba->getOpeningBalance();
            $accounts[$ba->getId()]['closingBalance'] = $ba->getClosingBalance();
            $accounts[$ba->getId()]['isClosed'] = $ba->getIsClosed();
            $accounts[$ba->getId()]['isJointAccount'] = $ba->getIsJointAccount();
        }
        return [
            'accounts' => $accounts,
            'opening-balance-total' => $this->getBalanceOnCourtOrderDateTotal(),
            'closing-balance-total' => $this->getBalanceOnCourtOrderDateTotal()
        ];
    }

    /**
     * Report summary, contains basic information about a report. Called via report.previousReportData so as not to
     * return everything.
     *
     * @return array
     */
    public function getReportSummary() {
        return [
            'type' => self::TYPE_NDR,
        ];
    }

    public function updateSectionsStatusCache(array $sectionIds)
    {
        //cache not needed for NDR
    }

    /**
     * Returns the translation key relating to the type of report. Hybrids identified to determine any suffix required
     * for the translation keys (translations are in 'report' domain)
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"ndr"})
     * @JMS\Type("string")
     * @return string
     */
    public function getReportTitle()
    {
        return 'ndr';
    }
}
