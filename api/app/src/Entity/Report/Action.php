<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'action')]
#[ORM\Entity]
class Action
{
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'action_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'action', targetEntity: Report::class)]
    private Report $report;

    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    #[ORM\Column(name: 'do_you_expect_decisions', type: 'string', length: 4, nullable: true)]
    private ?string $doYouExpectFinancialDecisions = null;

    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    #[ORM\Column(name: 'do_you_expect_decisions_details', type: 'text', nullable: true)]
    private ?string $doYouExpectFinancialDecisionsDetails = null;

    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    #[ORM\Column(name: 'do_you_have_concerns', type: 'string', length: 4, nullable: true)]
    private ?string $doYouHaveConcerns = null;

    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action'])]
    #[ORM\Column(name: 'do_you_have_concerns_details', type: 'text', nullable: true)]
    private ?string $doYouHaveConcernsDetails = null;

    public function __construct(Report $report)
    {
        $this->report = $report;
        $report->setAction($this);
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function getDoYouExpectFinancialDecisions(): ?string
    {
        return $this->doYouExpectFinancialDecisions;
    }

    public function getDoYouHaveConcerns(): ?string
    {
        return $this->doYouHaveConcerns;
    }

    public function setDoYouExpectFinancialDecisions(?string $doYouExpectFinancialDecisions): static
    {
        $this->doYouExpectFinancialDecisions = $doYouExpectFinancialDecisions;

        return $this;
    }

    public function setDoYouHaveConcerns(?string $doYouHaveConcerns): static
    {
        $this->doYouHaveConcerns = $doYouHaveConcerns;

        return $this;
    }

    public function getDoYouExpectFinancialDecisionsDetails(): ?string
    {
        return $this->doYouExpectFinancialDecisionsDetails;
    }

    public function getDoYouHaveConcernsDetails(): ?string
    {
        return $this->doYouHaveConcernsDetails;
    }

    public function setDoYouExpectFinancialDecisionsDetails(?string $doYouExpectFinancialDecisionsDetails): static
    {
        $this->doYouExpectFinancialDecisionsDetails = $doYouExpectFinancialDecisionsDetails;

        return $this;
    }

    public function setDoYouHaveConcernsDetails(?string $doYouHaveConcernsDetails): static
    {
        $this->doYouHaveConcernsDetails = $doYouHaveConcernsDetails;

        return $this;
    }

    public function cleanUpUnusedData(): void
    {
        if ($this->doYouExpectFinancialDecisions == 'no') {
            $this->doYouExpectFinancialDecisionsDetails = null;
        }

        if ($this->doYouHaveConcerns == 'no') {
            $this->doYouHaveConcernsDetails = null;
        }
    }
}
