<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User satisfaction scores
 *
 * @ORM\Table(name="satisfaction")
 * @ORM\Entity()
 */
class Satisfaction
{
    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="satisfaction_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int
     * @JMS\Type("integer")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="integer")
     */
    private $score;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="string", name="deputy_type", length=50)
     */
    private $deputyRole;

    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(type="string", name="report_type", length=9)
     * @Assert\Regex("/^10[0-9-]+$/")
     */
    private $reportType;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\Groups({"satisfaction"})
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

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
    public function getDeputyRole(): string
    {
        return $this->deputyRole;
    }

    /**
     * @param string $deputyRole
     * @return Satisfaction
     */
    public function setDeputyRole(string $deputyRole): Satisfaction
    {
        $this->deputyRole = $deputyRole;
        return $this;
    }

    /**
     * @return string
     */
    public function getReportType(): string
    {
        return $this->reportType;
    }

    /**
     * @param string $reportType
     * @return Satisfaction
     */
    public function setReportType(string $reportType): Satisfaction
    {
        $this->reportType = $reportType;
        return $this;
    }
}
