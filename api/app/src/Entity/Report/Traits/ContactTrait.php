<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Report;

trait ContactTrait
{
    /**
     * @var Collection<int,Contact>
     */
    #[JMS\Groups(['contact'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Contact>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Contact::class, cascade: ['persist', 'remove'])]
    private Collection $contacts;

    /**
     * Deputy reason for not having contacts. Required if no contacts are added.
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report'])]
    #[ORM\Column(name: 'reason_for_no_contacts', type: 'text', nullable: true)]
    private ?string $reasonForNoContacts = null;

    public function addContact(Contact $contacts): Report
    {
        $this->contacts[] = $contacts;

        return $this;
    }

    public function removeContact(Contact $contacts): void
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * @return Collection<int,Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function setReasonForNoContacts(?string $reasonForNoContacts): Report
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    public function getReasonForNoContacts(): ?string
    {
        return $this->reasonForNoContacts;
    }
}
