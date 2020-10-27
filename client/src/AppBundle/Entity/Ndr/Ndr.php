<?php

namespace AppBundle\Entity\Ndr;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Traits as NdrTraits;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\NdrStatusService;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Assert\Callback(callback="debtsValid", groups={"debts"})
 */
class Ndr implements ReportInterface
{
    use NdrTraits\ReportIncomeBenefitTrait;
    use NdrTraits\ReportDeputyExpenseTrait;
    use NdrTraits\ReportActionTrait;
    use NdrTraits\ReportMoreInfoTrait;
    use NdrTraits\ReportAgreeTrait;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"submit"})
     *
     * @var bool
     */
    private $submitted;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"start_date"})
     */
    private $startDate;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"submit"})
     */
    private $submitDate;

    /**
     * @JMS\Type("AppBundle\Entity\Client")
     *
     * @var Client
     */
    private $client;

    /**
     * @JMS\Type("AppBundle\Entity\Ndr\VisitsCare")
     *
     * @var VisitsCare|null
     */
    private $visitsCare;

    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\BankAccount>")
     *
     * @var BankAccount[]
     */
    private $bankAccounts = [];

    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\Debt>")
     * @JMS\Groups({"debt"})
     *
     * @var Debt[]
     */
    private $debts = [];

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-debt-management"})
     * @Assert\NotBlank(message="ndr.debt.debts-management.notBlank", groups={"ndr-debt-management"})
     *
     * @var string
     */
    private $debtManagement;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @Assert\NotBlank(message="ndr.debt.notBlank", groups={"debts"})
     *
     * @var string
     */
    private $hasDebts;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"debt"})
     *
     * @var float
     */
    private $debtsTotalAmount;

    /**
     * @JMS\Type("array<AppBundle\Entity\Ndr\Asset>")
     *
     * @var Asset[]
     */
    private $assets = [];

    /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"noAssetsToAdd"})
     *
     * @var bool
     */
    private $noAssetToAdd;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $reportTitle;

    /**
     * Currently used only for bottom navigator
     *
     * @return string
     */
    public function getType()
    {
        return 'ndr';
    }

    /**
     * @return float
     */
    public function getBankAccountsBalanceTotal()
    {
        $ret = 0;
        foreach ($this->getBankAccounts() as $bankAccount) {
            $ret += $bankAccount->getBalanceOnCourtOrderDate();
        }

        return $ret;
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

    /**
     * @return \DateTime|null
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
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return VisitsCare
     */
    public function getVisitsCare()
    {
        return $this->visitsCare ?: new VisitsCare();
    }

    /**
     * @param VisitsCare $visitsCare
     */
    public function setVisitsCare($visitsCare)
    {
        $this->visitsCare = $visitsCare;

        return $this;
    }

    /**
     * @return BankAccount[]
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }

    /**
     * @param BankAccount[] $bankAccounts
     */
    public function setBankAccounts($bankAccounts)
    {
        $this->bankAccounts = $bankAccounts;

        return $this;
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

        return $this;
    }

    public function isDue()
    {
        return false;
    }

    /**
     * Return the due date (calculated as court order date + 40 days).
     *
     * @return \DateTime|null $dueDate
     */
    public function getDueDate()
    {
        if (!$this->getStartDate() instanceof \DateTime) {
            return null;
        }

        $dueDate = clone $this->getStartDate();
        $dueDate->modify('+40 days');

        return $dueDate;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasBankAccountWithId($id)
    {
        foreach ($this->getBankAccounts() as $bankAccount) {
            if ($bankAccount->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return float
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
     * Get debts total value.
     *
     * @return float
     */
    public function getDebtsTotalValue()
    {
        $ret = 0;
        foreach ($this->getDebts() as $debt) {
            $ret += $debt->getAmount();
        }

        return $ret;
    }

    /**
     * @return Debt[]
     */
    public function getDebts()
    {
        return $this->debts;
    }

    /**
     * @param string $debtId
     *
     * @return Debt|null
     */
    public function getDebtById($debtId)
    {
        foreach ($this->getDebts() as $debt) {
            if ($debt->getDebtTypeId() == $debtId) {
                return $debt;
            }
        }

        return null;
    }

    /**
     * @return Debt[]
     */
    public function getDebtsWithValidAmount()
    {
        $debtsWithAValidAmount = array_filter($this->debts, function ($debt) {
            return !empty($debt->getAmount());
        });

        return $debtsWithAValidAmount;
    }

    /**
     * @param Debt[] $debts
     */
    public function setDebts($debts)
    {
        $this->debts = $debts;

        return $this;
    }

    /**
     * @return float
     */
    public function getDebtsTotalAmount()
    {
        return $this->debtsTotalAmount;
    }

    /**
     * @param float $debtsTotalAmount
     */
    public function setDebtsTotalAmount($debtsTotalAmount)
    {
        $this->debtsTotalAmount = $debtsTotalAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getHasDebts()
    {
        return $this->hasDebts;
    }

    /**
     * @param string $hasDebts
     */
    public function setHasDebts($hasDebts)
    {
        $this->hasDebts = $hasDebts;

        return $this;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function debtsValid(ExecutionContextInterface $context)
    {
        if ($this->getHasDebts() == 'yes'  && count($this->getDebtsWithValidAmount()) === 0) {
            $context->addViolation('ndr.debt.mustHaveAtLeastOneDebt');
        }
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
     * @param array $assets
     *
     * @return self
     */
    public function setAssets($assets)
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * @return Asset[]
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Used in the list view
     * AssetProperty is considered having title "Property"
     * Artwork, Antiques, Jewellery are grouped into "Artwork, antiques and jewellery".
     *
     * @return array $assets e.g. [Property => [asset1, asset2], Bonds=>[]...]
     */
    public function getAssetsGroupedByTitle()
    {
        // those needs to be grouped together
        $titleToGroupOverride = [
            'Artwork' => 'Artwork, antiques and jewellery',
            'Antiques' => 'Artwork, antiques and jewellery',
            'Jewellery' => 'Artwork, antiques and jewellery',
        ];

        $ret = [];
        foreach ($this->assets as $asset) {
            // select title
            if ($asset instanceof AssetProperty) {
                $title = 'Property';
            } elseif ($asset instanceof AssetOther) {
                $title = isset($titleToGroupOverride[$asset->getTitle()]) ?
                    $titleToGroupOverride[$asset->getTitle()] : $asset->getTitle();
            } else {
                throw new \RuntimeException('Could not identify assset type');
            }

            // add asset into "items" and sum total
            $ret[$title]['items'][$asset->getId()] = $asset;
            $ret[$title]['total'] = isset($ret[$title]['total'])
                ? $ret[$title]['total'] + $asset->getValueTotal()
                : $asset->getValueTotal();
        }

        // order categories
        ksort($ret);
        // foreach category, order assets by ID desc
        foreach ($ret as &$row) {
            krsort($row['items']);
        }

        return $ret;
    }

    /**
     * @return bool
     */
    public function getNoAssetToAdd()
    {
        return $this->noAssetToAdd;
    }

    /**
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
     * @param int $id
     *
     * @return bool
     */
    public function hasAssetWithId($id)
    {
        foreach ($this->getAssets() as $asset) {
            if ($asset->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get assets total value.
     *
     * @return float
     */
    public function getAssetsTotalValue()
    {
        $ret = 0;
        foreach ($this->getAssets() as $asset) {
            $ret += $asset->getValueTotal();
        }

        return $ret;
    }

    /**
     * @param string $format string where %s are submitDate Y-m-d, case number
     * @return string
     */
    public function createAttachmentName($format)
    {
        $attachmentName = sprintf(
            $format,
            is_null($this->getSubmitDate()) ? 'n-a-' : $this->getSubmitDate()->format('Y-m-d'),
            $this->getClient()->getCaseNumber()
        );

        return $attachmentName;
    }

    /**
     * @return NdrStatusService
     */
    public function getStatusService()
    {
        return new NdrStatusService($this);
    }

    /**
     * @return string
     */
    public function getReportTitle()
    {
        return $this->reportTitle;
    }

    /**
     * @param string $reportTitle
     * @return $this
     */
    public function setReportTitle($reportTitle)
    {
        $this->reportTitle = $reportTitle;
        return $this;
    }
}
