<?php declare(strict_types=1);

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class FullReviewChecklist
{
    /**
     * @var string
     * @JMS\Groups({"full-review-checklist"})
     * @JMS\Type("string")
     * @Assert\Type("string")
     */
    public $decisionExplanation;
}
