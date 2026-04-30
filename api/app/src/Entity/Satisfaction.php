<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse;
use OPG\Digideps\Backend\Repository\SatisfactionRepository;

/**
 * User satisfaction scores
 */
#[ORM\Table(name: 'satisfaction')]
#[ORM\Entity(repositoryClass: SatisfactionRepository::class)]
class Satisfaction
{
    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'satisfaction_id_seq', allocationSize: 1, initialValue: 1)]
    private $id;

    /**
     * @var int
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(type: 'integer')]
    private $score;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'comments', type: 'string', length: 1200, nullable: true)]
    private $comments;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'deputy_role', type: 'string', length: 50, nullable: true)]
    private $deputyrole;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'report_type', type: 'string', length: 9, nullable: true)]
    private $reporttype;


    #[JMS\Type('DateTime')]
    #[JMS\Groups(['satisfaction'])]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTime $created;

    #[JMS\Type('OPG\Digideps\Backend\Entity\UserResearch\UserResearchResponse')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(mappedBy: 'satisfaction', targetEntity: UserResearchResponse::class, cascade: ['persist', 'remove'])]
    private UserResearchResponse $userResearchResponse;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\Report')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(inversedBy: 'satisfaction', targetEntity: Report::class, cascade: ['persist'])]
    private ?Report $report = null;

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

    public function getReport(): ?Report
    {
        return $this->report;
    }

    public function setReport(?Report $report): Satisfaction
    {
        $this->report = $report;

        return $this;
    }
}
