<?php

namespace AppBundle\Entity\Report;

use DateTime;
use JMS\Serializer\Annotation as JMS;

class Satisfaction
{
    /**
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @JMS\Type("integer")
     */
    private $score;

    /**
     * @JMS\Type("string")
     */
    private $comments;

    /**
     * @JMS\Type("string")
     */
    private $deputyrole;

    /**
     * @JMS\Type("string")
     */
    private $reporttype;

    /**
     * @JMS\Type("DateTime")
     */
    private $created;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Satisfaction
     */
    public function setId(int $id): Satisfaction
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return Satisfaction
     */
    public function setScore(int $score): Satisfaction
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     * @return Satisfaction
     */
    public function setComments(string $comments): Satisfaction
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyrole()
    {
        return $this->deputyrole;
    }

    /**
     * @param string $deputyrole
     * @return string
     */
    public function setDeputyrole($deputyrole): string
    {
        $this->deputyrole = $deputyrole;
        return $this;
    }

    /**
     * @return string
     */
    public function getReporttype()
    {
        return $this->reporttype;
    }

    /**
     * @param string $reporttype
     * @return string
     */
    public function setReporttype($reporttype): string
    {
        $this->reporttype = $reporttype;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     * @return Satisfaction
     */
    public function setCreated($created): Satisfaction
    {
        $this->created = $created;
        return $this;
    }

}
