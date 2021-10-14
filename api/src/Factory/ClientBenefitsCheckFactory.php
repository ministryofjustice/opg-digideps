<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Report;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class ClientBenefitsCheckFactory
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createFromFormData(array $formData)
    {
        $report = $this->em->find(Report::class, $formData['report_id']);
        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new DateTime($formData['date_last_checked_entitlement']) : null;

        return (new ClientBenefitsCheck(Uuid::uuid4()))
            ->setReport($report)
            ->setCreated(new DateTime())
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'])
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation']);
    }
}
