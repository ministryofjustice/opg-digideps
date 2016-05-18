<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class FeedbackReport
{
    /**
     * @Assert\NotBlank(message="feedbackAfterReport.satisfactionLevel.notEmpty")
     * @JMS\Type("string")
     */
    private $satisfactionLevel;

    public function getSatisfactionLevel()
    {
        return $this->satisfactionLevel;
    }

    public function setSatisfactionLevel($satisfactionLevel)
    {
        $this->satisfactionLevel = $satisfactionLevel;

        return $this;
    }
}
