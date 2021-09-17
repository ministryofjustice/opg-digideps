<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Report\ClientBenefitsCheck;
use DateTime;
use Ramsey\Uuid\Uuid;

class ClientBenefitsCheckFactory
{
    public function createFromFormData(array $formData)
    {
        $dateLastChecked = isset($formData['date_last_checked_entitlement']) ? new DateTime($formData['date_last_checked_entitlement']) : null;

        return (new ClientBenefitsCheck(Uuid::uuid4()))
            ->setCreated(new DateTime())
            ->setWhenLastCheckedEntitlement($formData['when_last_checked_entitlement'])
            ->setDateLastCheckedEntitlement($dateLastChecked)
            ->setNeverCheckedExplanation($formData['never_checked_explanation']);
    }
}
