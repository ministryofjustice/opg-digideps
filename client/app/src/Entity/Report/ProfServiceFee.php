<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[JMS\Discriminator(field: 'fee_type_id', map: ['current' => 'OPG\Digideps\Frontend\Entity\Report\ProfServiceFeeCurrent'])]
abstract class ProfServiceFee
{
    use HasReportTrait;

    public const string TYPE_ASSESSED_FEE = 'assessed';
    public const string TYPE_FIXED_FEE = 'fixed';

    public const string TYPE_PREVIOUS_FEE = 'previous';
    public const string TYPE_CURRENT_FEE = 'current';
    public const string TYPE_ESTIMATED_FEE = 'estimated';

    #[JMS\Type('integer')]
    #[JMS\Groups(['prof-service-fees'])]
    private ?int $id = null;

    /**
     * Hold service type.
     *
     * If the order or any key is added, update the ReportControllerTest, hardcoded on position and number
     *  in order to keep it simple
     *
     * @var array<string, bool>
     */
    public static array $serviceTypeIds = [
        'annual-report' => false,
        'annual-management-interim' => false,
        'annual-management-final' => false,
        'appointment' => false,
        'conveyancing' => false,
        'tax-returns' => false,
        'trust-applications' => false,
        'other-costs' => false,
    ];

    /**
     * @var ?string fixed|assessed
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    #[Assert\NotBlank(message: 'profServiceFee.assessedOrFixed.notBlank', groups: ['prof-service-fee-details-type'])]
    private ?string $assessedOrFixed = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    #[Assert\NotBlank(message: 'fee.otherFeeDetails.notBlank', groups: ['other-prof-service-fees'])]
    private ?string $otherFeeDetails = null;

    /**
     * @var ?string a value in self::serviceTypeIds
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees', 'prof-service-fee-serviceType'])]
    #[Assert\NotBlank(message: 'profServiceFee.serviceType.notBlank', groups: ['prof-service-fee-type'])]
    private ?string $serviceTypeId = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    #[Assert\NotBlank(message: 'profServiceFee.amountCharged.notBlank', groups: ['prof-service-fee-details-type'])]
    #[Assert\Range(notInRangeMessage: 'fee.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['prof-service-fee-details-type'])]
    private ?float $amountCharged = null;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    #[Assert\NotBlank(message: 'profServiceFee.paymentReceived.notBlank', groups: ['prof-service-fee-details-type'])]
    private ?string $paymentReceived = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-service-fees'])]
    #[Assert\NotBlank(message: 'profServiceFee.amountReceived.notBlank', groups: ['prof-service-fee-details-type-payment-received'])]
    #[Assert\Range(notInRangeMessage: 'fee.amount.notInRangeMessage', min: 0, max: 100000000000, groups: ['prof-service-fee-details-type-payment-received'])]
    private ?float $amountReceived = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['prof-service-fees'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'profServiceFee.paymentReceivedDate.invalidMessage', groups: ['prof-service-fee-details-type-payment-received'])]
    #[Assert\LessThanOrEqual('today', message: 'profServiceFee.paymentReceivedDate.notInTheFuture', groups: ['prof-service-fee-details-type-payment-received'])]
    #[Assert\NotBlank(message: 'profServiceFee.paymentReceivedDate.notBlank', groups: ['prof-service-fee-details-type-payment-received'])]
    private ?\DateTime $paymentReceivedDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAssessedOrFixed(): ?string
    {
        return $this->assessedOrFixed;
    }

    public function setAssessedOrFixed(?string $assessedOrFixed): static
    {
        $this->assessedOrFixed = $assessedOrFixed;

        return $this;
    }

    abstract public function getFeeTypeId(): ?string;

    public function getOtherFeeDetails(): ?string
    {
        return $this->otherFeeDetails;
    }

    public function setOtherFeeDetails(?string $otherFeeDetails): static
    {
        $this->otherFeeDetails = $otherFeeDetails;

        return $this;
    }

    public function getAmountCharged(): float
    {
        return $this->amountCharged ?? 0.0;
    }

    public function setAmountCharged(?float $amountCharged): static
    {
        $this->amountCharged = $amountCharged;

        return $this;
    }

    public function getAmountReceived(): float
    {
        return $this->amountReceived ?? 0.0;
    }

    public function setAmountReceived(?float $amountReceived): static
    {
        $this->amountReceived = $amountReceived;

        return $this;
    }

    public function getPaymentReceivedDate(): ?\DateTime
    {
        return $this->paymentReceivedDate;
    }

    public function setPaymentReceivedDate(?\DateTime $paymentReceivedDate): static
    {
        $this->paymentReceivedDate = $paymentReceivedDate;

        return $this;
    }

    public function getPaymentReceived(): ?string
    {
        return $this->paymentReceived;
    }

    public function setPaymentReceived(?string $paymentReceived): static
    {
        $this->paymentReceived = $paymentReceived;

        return $this;
    }

    public function getServiceTypeId(): ?string
    {
        return $this->serviceTypeId;
    }

    public function setServiceTypeId(string $serviceTypeId): static
    {
        $this->serviceTypeId = $serviceTypeId;

        return $this;
    }

    public function isCurrentFee(): bool
    {
        return $this->getFeeTypeId() == self::TYPE_CURRENT_FEE;
    }

    public function isPreviousFee(): bool
    {
        return $this->getFeeTypeId() == self::TYPE_PREVIOUS_FEE;
    }

    public function isEstimatedFee(): bool
    {
        return $this->getFeeTypeId() == self::TYPE_ESTIMATED_FEE;
    }
}
