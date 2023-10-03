<?php

declare(strict_types=1);

namespace App\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class FullReviewChecklist
{
    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $fullBankStatementsExist;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $anyLodgingConcerns;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $spendingAcceptable;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $expensesReasonable;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $giftingReasonable;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $debtManageable;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no", "na"})
     * @Assert\NotBlank
     */
    public $anySpendingConcerns;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Choice({"yes", "no"})
     * @Assert\NotBlank
     */
    public $needReferral;

    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Type("string")
     */
    public $decisionExplanation;
}
