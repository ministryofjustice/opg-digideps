<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'moreDetailsValidate', groups: ['prof-deputy-estimate-costs'])]
class ProfDeputyEstimateCost
{
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private $profDeputyEstimateCostTypeId;

    /**
     * @var string decimal
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[Assert\Type(type: 'numeric', message: 'profDeputyEstimateCost.amount.notNumeric', groups: ['prof-deputy-estimate-costs'])]
    #[Assert\Range(notInRangeMessage: 'profDeputyEstimateCost.amount.notInRangeMessage', min: 0, max: 100000000, groups: ['prof-deputy-estimate-costs'])]
    private $amount;

    /**
     * @var string
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private $hasMoreDetails;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    private $moreDetails;

    /**
     * ProfDeputyEstimateCost constructor.
     *
     * @param $profDeputyEstimateCostTypeId
     * @param string $amount decimal
     * @param string  $hasMoreDetails
     * @param string  $moreDetails
     */
    public function __construct($profDeputyEstimateCostTypeId, $amount, $hasMoreDetails, $moreDetails)
    {
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @return mixed
     */
    public function getProfDeputyEstimateCostTypeId()
    {
        return $this->profDeputyEstimateCostTypeId;
    }

    /**
     * @param $profDeputyEstimateCostTypeId
     */
    public function setProfDeputyEstimateCostTypeId($profDeputyEstimateCostTypeId): static
    {
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;

        return $this;
    }

    /**
     * @return string decimal
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount decimal
     */
    public function setAmount($amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param string $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails): static
    {
        $this->hasMoreDetails = $hasMoreDetails;

        return $this;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails): static
    {
        $this->moreDetails = $moreDetails;

        return $this;
    }

    public function moreDetailsValidate(ExecutionContextInterface $context): void
    {
        if (!$this->getHasMoreDetails()) {
            return;
        }

        $hasMoreDetails = trim($this->getMoreDetails(), " \n") ? true : false;

        if ($this->getAmount() && !$hasMoreDetails) {
            $context->buildViolation('profDeputyEstimateCost.moreDetails.notBlank')->atPath('moreDetails')->addViolation();
        }
    }
}
