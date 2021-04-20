<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\User;
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
     * @JMS\Type("User")
     */
    private User $user;

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
     * @return Satisfaction
     */
    public function setDeputyrole($deputyrole): Satisfaction
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
     * @return Satisfaction
     */
    public function setReporttype($reporttype): Satisfaction
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

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Satisfaction
     */
    public function setUser(User $user): Satisfaction
    {
        $this->user = $user;
        return $this;
    }
}
