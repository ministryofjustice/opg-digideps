<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'moreDetailsValidate', groups: ['prof-deputy-other-costs'])]
class ProfDeputyOtherCost
{
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    private $profDeputyOtherCostTypeId;

    /**
     * @var string|null decimal
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[Assert\Type(type: 'numeric', message: 'profDeputyOtherCost.amount.notNumeric', groups: ['prof-deputy-other-costs'])]
    #[Assert\Range(min: 0, max: 100000000000, notInRangeMessage: 'profDeputyOtherCost.amount.notInRangeMessage', groups: ['prof-deputy-other-costs'])]
    private ?string $amount;

    /**
     * @var bool
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[JMS\Type('boolean')]
    private bool $hasMoreDetails;

    /**
     * @var string|null
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[JMS\Type('string')]
    private ?string $moreDetails;

    /**
     * ProfDeputyOtherCost constructor.
     *
     * @param $profDeputyOtherCostTypeId
     * @param string|null $amount decimal
     * @param bool $hasMoreDetails
     * @param string|null $moreDetails
     */
    public function __construct($profDeputyOtherCostTypeId, ?string $amount, bool $hasMoreDetails, ?string $moreDetails)
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyOtherCostTypeId()
    {
        return $this->profDeputyOtherCostTypeId;
    }

    /**
     * @param $profDeputyOtherCostTypeId
     */
    public function setProfDeputyOtherCostTypeId($profDeputyOtherCostTypeId): void
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
    }

    /**
     * @return string|null decimal
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @param string $amount decimal
     */
    public function setAmount(?string $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return bool
     */
    public function getHasMoreDetails(): bool
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param bool $hasMoreDetails
     */
    public function setHasMoreDetails(bool $hasMoreDetails): void
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return string|null
     */
    public function getMoreDetails(): ?string
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails(?string $moreDetails): void
    {
        $this->moreDetails = $moreDetails;
    }

    public function moreDetailsValidate(ExecutionContextInterface $context): void
    {
        $hasMoreDetails = false;
        if (!$this->getHasMoreDetails()) {
            return;
        }

        if ($this->getMoreDetails() !== null) {
            $hasMoreDetails = (bool)trim($this->getMoreDetails(), " \n");
        }

        if ($this->getAmount() && !$hasMoreDetails) {
            $context->buildViolation('profDeputyOtherCost.moreDetails.notBlank')->atPath('moreDetails')->addViolation();
        }
    }
}
