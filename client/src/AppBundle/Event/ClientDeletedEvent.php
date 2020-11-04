<?php declare(strict_types=1);


namespace AppBundle\Event;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ClientDeletedEvent extends Event
{
    const NAME = 'client.deleted';

    /** @var string */
    private $trigger;
    private $caseNumber;
    private $dischargedByEmail;
    private $dischargedDeputyName;

    /** @var \DateTime */
    private $deputyshipStartDate;

    public function __construct(Client $client, User $currentUser, User $deputy, string $trigger)
    {
        $this->setCaseNumber($client->getCaseNumber());
        $this->setDeputyshipStartDate($client->getCourtDate());
        $this->setDischargedByEmail($currentUser->getEmail());
        $this->setDischargedDeputyName($deputy->getFullName());
        $this->setTrigger($trigger);
    }

    /**
     * @return string
     */
    public function getTrigger(): string
    {
        return $this->trigger;
    }

    /**
     * @param string $trigger
     * @return ClientDeletedEvent
     */
    public function setTrigger(string $trigger): ClientDeletedEvent
    {
        $this->trigger = $trigger;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     * @return ClientDeletedEvent
     */
    public function setCaseNumber(string $caseNumber): ClientDeletedEvent
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getDischargedByEmail(): string
    {
        return $this->dischargedByEmail;
    }

    /**
     * @param string $dischargedByEmail
     * @return ClientDeletedEvent
     */
    public function setDischargedByEmail(string $dischargedByEmail): ClientDeletedEvent
    {
        $this->dischargedByEmail = $dischargedByEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getDischargedDeputyName(): string
    {
        return $this->dischargedDeputyName;
    }

    /**
     * @param string $dischargedDeputyName
     * @return ClientDeletedEvent
     */
    public function setDischargedDeputyName(string $dischargedDeputyName): ClientDeletedEvent
    {
        $this->dischargedDeputyName = $dischargedDeputyName;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeputyshipStartDate(): \DateTime
    {
        return $this->deputyshipStartDate;
    }

    /**
     * @param \DateTime $deputyshipStartDate
     * @return ClientDeletedEvent
     */
    public function setDeputyshipStartDate(\DateTime $deputyshipStartDate): ClientDeletedEvent
    {
        $this->deputyshipStartDate = $deputyshipStartDate;
        return $this;
    }
}
