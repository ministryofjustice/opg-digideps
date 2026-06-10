<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use OPG\Digideps\Backend\Entity\Report\ProfDeputyEstimateCost;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

trait ReportProfDeputyCostsEstimateTrait
{
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-costs-estimate-how-charged'])]
    #[ORM\Column(name: 'prof_dc_estimate_hc', type: 'string', length: 10, nullable: true)]
    private ?string $profDeputyCostsEstimateHowCharged = null;

    /**
     * @var Collection<int,ProfDeputyEstimateCost>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\ProfDeputyEstimateCost>')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: ProfDeputyEstimateCost::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $profDeputyEstimateCosts;

    #[JMS\Groups(['prof-deputy-costs-estimate-more-info'])]
    #[ORM\Column(name: 'prof_dc_estimate_more_info', type: 'string', length: 3, nullable: true)]
    private ?string $profDeputyCostsEstimateHasMoreInfo = null;

    #[JMS\Groups(['prof-deputy-costs-estimate-more-info'])]
    #[ORM\Column(name: 'prof_dc_estimate_more_info_details', type: 'text', nullable: true)]
    private ?string $profDeputyCostsEstimateMoreInfoDetails = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-management-costs'])]
    #[JMS\SerializedName('prof_deputy_management_cost_amount')]
    #[ORM\Column(name: 'prof_dc_estimate_management_cost', type: 'float', precision: 14, scale: 2, nullable: true)]
    private ?float $profDeputyCostsEstimateManagementCostAmount = null;

    /**
     * Hold prof deputy estimate costs type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @var array<array{'typeId': string, 'hasMoreDetails': bool}>
     */
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    public static array $profDeputyEstimateCostTypeIds = [
        ['typeId' => 'contact-client', 'hasMoreDetails' => false],
        ['typeId' => 'contact-case-manager-carers', 'hasMoreDetails' => false],
        ['typeId' => 'contact-others', 'hasMoreDetails' => false],
        ['typeId' => 'forms-documents', 'hasMoreDetails' => false],
    ];

    /**
     * @return Collection<int,ProfDeputyEstimateCost>
     */
    public function getProfDeputyEstimateCosts(): Collection
    {
        return $this->profDeputyEstimateCosts;
    }

    /**
     * @param Collection<int,ProfDeputyEstimateCost> $collection
     */
    public function setProfDeputyEstimateCosts(Collection $collection): static
    {
        $this->profDeputyEstimateCosts = $collection;

        return $this;
    }

    public function addProfDeputyEstimateCost(ProfDeputyEstimateCost $profDeputyEstimateCost): static
    {
        $this->profDeputyEstimateCosts->add($profDeputyEstimateCost);

        return $this;
    }

    public function getProfDeputyEstimateCostByTypeId(string $typeId): ?ProfDeputyEstimateCost
    {
        return $this->getProfDeputyEstimateCosts()->filter(
            fn (ProfDeputyEstimateCost $profDeputyEstimateCost): bool => $profDeputyEstimateCost->getProfDeputyEstimateCostTypeId() == $typeId
        )->first() ?: null;
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('prof_deputy_estimate_cost_type_ids')]
    #[JMS\Type('array')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    public static function getProfDeputyEstimateCostTypeIds(): array
    {
        return self::$profDeputyEstimateCostTypeIds;
    }

    public function getProfDeputyCostsEstimateHowCharged(): ?string
    {
        return $this->profDeputyCostsEstimateHowCharged;
    }

    public function setProfDeputyCostsEstimateHowCharged(?string $profDeputyCostsEstimateHowCharged): static
    {
        $this->profDeputyCostsEstimateHowCharged = $profDeputyCostsEstimateHowCharged;

        return $this;
    }

    public function getProfDeputyCostsEstimateHasMoreInfo(): ?string
    {
        return $this->profDeputyCostsEstimateHasMoreInfo;
    }

    public function setProfDeputyCostsEstimateHasMoreInfo(?string $profDeputyCostsEstimateHasMoreInfo): static
    {
        $this->profDeputyCostsEstimateHasMoreInfo = $profDeputyCostsEstimateHasMoreInfo;

        return $this;
    }

    public function getProfDeputyCostsEstimateMoreInfoDetails(): ?string
    {
        return $this->profDeputyCostsEstimateMoreInfoDetails;
    }

    public function setProfDeputyCostsEstimateMoreInfoDetails(?string $profDeputyCostsEstimateMoreInfoDetails): static
    {
        $this->profDeputyCostsEstimateMoreInfoDetails = $profDeputyCostsEstimateMoreInfoDetails;

        return $this;
    }

    #[JMS\Groups(['prof-deputy-estimate-management-costs'])]
    #[JMS\SerializedName('prof_deputy_management_cost_amount')]
    #[JMS\Type('double')]
    public function getProfDeputyCostsEstimateManagementCostAmount(): ?float
    {
        return $this->profDeputyCostsEstimateManagementCostAmount;
    }

    public function setProfDeputyCostsEstimateManagementCostAmount(?float $profDeputyCostsEstimateManagementCostAmount): static
    {
        $this->profDeputyCostsEstimateManagementCostAmount = $profDeputyCostsEstimateManagementCostAmount;

        return $this;
    }
}
