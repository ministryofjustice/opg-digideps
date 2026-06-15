<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;

#[ORM\Table(name: 'contact')]
#[ORM\Entity, ORM\HasLifecycleCallbacks]
class Contact
{
    use CreateUpdateTimestamps;

    #[JMS\Type('integer')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'contact_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'contact_name', type: 'string', length: 255, nullable: true)]
    private ?string $contactName = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'address', type: 'string', length: 200, nullable: true)]
    private ?string $address = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'address2', type: 'string', length: 200, nullable: true)]
    private ?string $address2 = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'county', type: 'string', length: 200, nullable: true)]
    private ?string $county = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'postcode', type: 'string', length: 10, nullable: true)]
    private ?string $postcode = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'country', type: 'string', length: 10, nullable: true)]
    private ?string $country = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'explanation', type: 'text', nullable: true)]
    private ?string $explanation = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'relationship', type: 'string', length: 100, nullable: true)]
    private ?string $relationship = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['contact'])]
    #[ORM\Column(name: 'phone1', type: 'string', length: 20, nullable: true)]
    private ?string $phone1 = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'contacts')]
    private Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
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

    public function setContactName(?string $contactName): static
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setPostcode(?string $postcode): static
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setExplanation(?string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setRelationship(?string $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getRelationship(): ?string
    {
        return $this->relationship;
    }

    public function setPhone1(?string $phone1): static
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
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

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress2(?string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setCounty(?string $county): static
    {
        $this->county = $county;

        return $this;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getContacts()->count() === 1) {
            $this->getReport()->setReasonForNoContacts(null);
        }
    }
}
