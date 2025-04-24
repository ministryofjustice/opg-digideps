<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Contact;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait ContactTrait
{
    /**
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Contact", mappedBy="report", cascade={"persist", "remove"})
     */
    #[JMS\Groups(['contact'])]
    #[JMS\Type('ArrayCollection<App\Entity\Report\Contact>')]
    private $contacts;

    /**
     * @var string deputy reason for not having contacts. Required if no contacts are added
     *
     *
     *
     * @ORM\Column(name="reason_for_no_contacts", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report'])]
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
     * @return \Doctrine\Common\Collections\Collection
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
