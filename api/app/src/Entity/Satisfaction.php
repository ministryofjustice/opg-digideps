<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\UserResearch\UserResearchResponse;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * User satisfaction scores.
 *
 * @ORM\Table(name="satisfaction")
 *
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="App\Repository\SatisfactionRepository")
 */
class Satisfaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="satisfaction_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['satisfaction'])]
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['satisfaction'])]
    private $score;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="comments", length=1200, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="deputy_role", length=50, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    private $deputyrole;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="report_type", length=9, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    private $reporttype;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    #[JMS\Type('DateTime')]
    #[JMS\Groups(['satisfaction'])]
    private \DateTime $created;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\UserResearch\UserResearchResponse", mappedBy="satisfaction", cascade={"persist", "remove"})
     */
    #[JMS\Type('App\Entity\UserResearch\UserResearchResponse')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    private UserResearchResponse $userResearchResponse;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Report\Report", inversedBy="satisfaction", cascade={"persist"})
     */
    #[JMS\Type('App\Entity\Report\Report')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    private ?Report $report = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Ndr\Ndr", inversedBy="satisfaction", cascade={"persist"})
     */
    #[JMS\Type('App\Entity\Ndr\Ndr')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    private ?Ndr $ndr = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Satisfaction
    {
        $this->id = $id;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): Satisfaction
    {
        $this->score = $score;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): Satisfaction
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyrole(): ?string
    {
        return $this->deputyrole;
    }

    public function setDeputyrole(string $deputyrole): Satisfaction
    {
        $this->deputyrole = $deputyrole;

        return $this;
    }

    /**
     * @return string
     */
    public function getReporttype(): ?string
    {
        return $this->reporttype;
    }

    public function setReporttype(string $reporttype): Satisfaction
    {
        $this->reporttype = $reporttype;

        return $this;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): Satisfaction
    {
        $this->created = $created;

        return $this;
    }

    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): Satisfaction
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): Satisfaction
    {
        $this->report = $report;

        return $this;
    }

    /**
     * @return Ndr
     */
    public function getNdr(): ?Ndr
    {
        return $this->ndr;
    }

    public function setNdr(?Ndr $ndr): Satisfaction
    {
        $this->ndr = $ndr;

        return $this;
    }
}
