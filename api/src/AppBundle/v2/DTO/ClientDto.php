<?php

namespace AppBundle\v2\DTO;

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
    private $address;

    /** @var string */
    private $address2;

    /** @var string */
    private $county;

    /** @var string */
    private $postcode;

    /** @var string */
    private $country;

    /** @var string */
    private $phone;

    /** @var string */
    private $email;

    /** @var \DateTime */
    private $dateOfBirth;

    /** @var \DateTime */
    private $archivedAt;

    /** @var int */
    private $reportCount = 0;

    /** @var NdrDto */
    private $ndr;

    /** @var array */
    private $reports;

    /** @var OrganisationDto */
    private $organisation;

    /** @var NamedDeputyDto */
    private $namedDeputy;

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

    /** @return string */
    public function getAddress()
    {
        return $this->address;
    }

    /** @return string */
    public function getAddress2()
    {
        return $this->address2;
    }

    /** @return string */
    public function getCounty()
    {
        return $this->county;
    }

    /** @return string */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /** @return string */
    public function getCountry()
    {
        return $this->country;
    }

    /** @return string */
    public function getPhone()
    {
        return $this->phone;
    }

    /** @return string */
    public function getEmail()
    {
        return $this->email;
    }

    /** @return \DateTime */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return \DateTime
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
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
     * @return OrganisationDto
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return NamedDeputyDto
     */
    public function getNamedDeputy()
    {
        return $this->namedDeputy;
    }

    /**
     * @param int $id
     * @return ClientDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $caseNumber
     * @return ClientDto
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;
        return $this;
    }

    /**
     * @param string $firstName
     * @return ClientDto
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param string $lastName
     * @return ClientDto
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param string $address
     * @return ClientDto
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @param string $address2
     * @return ClientDto
     */
    public function setAddress2(string $address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @param string $county
     * @return ClientDto
     */
    public function setCounty(string $county)
    {
        $this->county = $county;
        return $this;
    }

    /**
     * @param string $postcode
     * @return ClientDto
     */
    public function setPostcode(string $postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @param string $country
     * @return ClientDto
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @param string $phone
     * @return ClientDto
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @param string $email
     * @return ClientDto
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);
        return $this;
    }

    /**
     * @param \DateTime $dateOfBirth
     * @return ClientDto
     */
    public function setDateOfBirth(\DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     * @param \DateTime $archivedAt
     * @return ClientDto
     */
    public function setArchivedAt($archivedAt)
    {
        $this->archivedAt = $archivedAt;
        return $this;
    }

    /**
     * @param int $reportCount
     * @return ClientDto
     */
    public function setReportCount($reportCount)
    {
        $this->reportCount = $reportCount;
        return $this;
    }

    /**
     * @param NdrDto $ndr
     * @return ClientDto
     */
    public function setNdr(NdrDto $ndr)
    {
        $this->ndr = $ndr;
        return $this;
    }

    /**
     * @param array $reports
     * @return ClientDto
     */
    public function setReports(array $reports)
    {
        $this->reports = $reports;
        return $this;
    }

    /**
     * @param OrganisationDto $organisation
     * @return ClientDto
     */
    public function setOrganisation(OrganisationDto $organisation)
    {
        $this->organisation = $organisation;
        return $this;
    }

    /**
     * @param NamedDeputyDto $namedDeputy
     * @return $this
     */
    public function setNamedDeputy(NamedDeputyDto $namedDeputy)
    {
        $this->namedDeputy = $namedDeputy;
        return $this;
    }
}
