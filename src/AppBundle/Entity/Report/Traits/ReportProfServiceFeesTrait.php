<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Fee;
use AppBundle\Entity\Report\ProfServiceFee;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

trait ReportProfServiceFeesTrait
{
    /**
     * @JMS\Type("array<AppBundle\Entity\Report\ProfServiceFee>")
     * @JMS\Groups({"prof-service-fees"})
     *
     * @var ProfServiceFee[]
     */
    private $profServiceFees = [];

    /**
     * @var string yes/no
     *
     * @JMS\Type("string")
     * @JMS\Groups({"current-prof-payments-received"})
     * @Assert\NotBlank(message="prof.fees.currentProfPaymentsReceived.notBlank", groups={"current-prof-fees-received-choice"})
     */
    private $currentProfPaymentsReceived;

    /**
     * @return string
     */
    public function getCurrentProfPaymentsReceived()
    {
        return $this->currentProfPaymentsReceived;
    }

    /**
     * @param string $currentProfPaymentsReceived
     */
    public function setCurrentProfPaymentsReceived($currentProfPaymentsReceived)
    {
        $this->currentProfPaymentsReceived = $currentProfPaymentsReceived;
    }
}
