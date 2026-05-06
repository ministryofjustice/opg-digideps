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
    #[JMS\Groups(['contact'])]
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Contact>')]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Contact::class, cascade: ['persist', 'remove'])]
    private $contacts;

    /**
     * @var string deputy reason for not having contacts. Required if no contacts are added
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report'])]
    #[ORM\Column(name: 'reason_for_no_contacts', type: 'text', nullable: true)]
    private $reasonForNoContacts;

    /**
     * Add contacts.
     *
     * @return Report
     */
    public function addContact(Contact $contacts)
    {
        $this->contacts[] = $contacts;

        return $this;
    }

    /**
     * Remove contacts.
     */
    public function removeContact(Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts.
     *
     * @return Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set reasonForNoContact.
     *
     * @param string $reasonForNoContacts
     *
     * @return Report
     */
    public function setReasonForNoContacts($reasonForNoContacts)
    {
        $this->reasonForNoContacts = $reasonForNoContacts;

        return $this;
    }

    /**
     * Get reasonForNoContacts.
     *
     * @return string
     */
    public function getReasonForNoContacts()
    {
        return $this->reasonForNoContacts;
    }
}
