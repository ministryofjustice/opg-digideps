<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;

#[ORM\Table(name: 'decision')]
#[ORM\Entity, ORM\HasLifecycleCallbacks]
class Decision
{
    use CreateUpdateTimestamps;

    #[JMS\Groups(['decision'])]
    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'decision_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Groups(['decision'])]
    #[JMS\Type('string')]
    #[ORM\Column(type: 'text')]
    private string $description;

    #[JMS\Groups(['decision'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'client_involved_boolean', type: 'boolean')]
    private bool $clientInvolvedBoolean;

    #[JMS\Groups(['decision'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'client_involved_details', type: 'text', nullable: true)]
    private ?string $clientInvolvedDetails = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'decisions')]
    private Report $report;

    public function __construct(Report $report, bool $clientInvolvedBoolean = false, string $description = '')
    {
        $this->report = $report;
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;
        $this->description = $description;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setClientInvolvedBoolean(bool $clientInvolvedBoolean): void
    {
        $this->clientInvolvedBoolean = $clientInvolvedBoolean;
    }

    public function getClientInvolvedBoolean(): bool
    {
        return $this->clientInvolvedBoolean;
    }

    public function setClientInvolvedDetails(?string $clientInvolvedDetails): void
    {
        $this->clientInvolvedDetails = $clientInvolvedDetails;
    }

    public function getClientInvolvedDetails(): ?string
    {
        return $this->clientInvolvedDetails;
    }

    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getDecisions()->count() === 1) {
            $this->getReport()->setReasonForNoDecisions(null);
        }
    }
}
