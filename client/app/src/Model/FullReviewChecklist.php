<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Model;

use JMS\Serializer\Annotation as JMS;

class FullReviewChecklist
{
    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $fullBankStatementsExist = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $anyLodgingConcerns = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $spendingAcceptable = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $expensesReasonable = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $giftingReasonable = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $debtManageable = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $anySpendingConcerns = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $needReferral = null;

    #[JMS\Groups(['full-review-checklist'])]
    #[JMS\Type('string')]
    public ?string $decisionExplanation = null;
}
