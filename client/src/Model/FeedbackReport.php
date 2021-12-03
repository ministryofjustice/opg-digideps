<?php

namespace App\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class FeedbackReport
{
    /**
     * @Assert\NotBlank(message="feedbackAfterReport.satisfactionLevel.notEmpty")
     * @JMS\Type("string")
     */
    private $satisfactionLevel;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $comments;

    public function getSatisfactionLevel()
    {
        return $this->satisfactionLevel;
    }

    public function setSatisfactionLevel($satisfactionLevel)
    {
        $this->satisfactionLevel = $satisfactionLevel;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(string $comments): FeedbackReport
    {
        $this->comments = $comments;

        return $this;
    }
}
