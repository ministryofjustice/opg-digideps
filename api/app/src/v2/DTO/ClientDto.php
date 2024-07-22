<?php

namespace App\v2\DTO;

use App\Entity\Organisation;

class ClientDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $caseNumber;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $email;

    /** @var \DateTime */
    private $archivedAt;

    /** @var \DateTime */
    private $deletedAt;

    /** @var int */
    private $reportCount = 0;

    /** @var NdrDto */
    private $ndr;

    /** @var array */
    private $reports;

    /** @var Organisation */
    private $organisation;

    /** @var DeputyDto[] */
    private $deputies;

    /** @var array */
    private $users;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @return int
     */
    public function getReportCount()
    {
        return $this->reportCount;
    }

    /**
     * @return NdrDto
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @return array
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @return array
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return DeputyDto[]
     */
    public function getDeputies()
    {
        return $this->deputies;
    }

    /**
     * @param int $id
     *
     * @return ClientDto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $caseNumber
     *
     * @return ClientDto
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @param string $firstName
     *
     * @return ClientDto
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     *
     * @return ClientDto
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $email
     *
     * @return ClientDto
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * @param \DateTime $archivedAt
     *
     * @return ClientDto
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    /**
     * @param \DateTime $deletedAt
     *
     * @return ClientDto
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @param int $reportCount
     *
     * @return ClientDto
     */
    public function setReportCount($reportCount)
    {
        $this->reportCount = $reportCount;

        return $this;
    }

    /**
     * @return ClientDto
     */
    public function setNdr(NdrDto $ndr)
    {
        $this->ndr = $ndr;

        return $this;
    }

    /**
     * @return ClientDto
     */
    public function setReports(array $reports)
    {
        $this->reports = $reports;

        return $this;
    }

    /**
     * @return ClientDto
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * @return $this
     */
    public function setDeputies(array $deputies)
    {
        $this->deputies = $deputies;

        return $this;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers(array $users): ClientDto
    {
        $this->users = $users;

        return $this;
    }
}
