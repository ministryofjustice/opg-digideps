<?php

namespace AppBundle\v2\DTO;

class ClientDto implements \JsonSerializable
{
    private $id;
    private $caseNumber;
    private $firstName;
    private $lastName;
    private $email;
    private $reportCount;
    private $ndrId;

    /**
     * @param $id
     * @param $caseNumber
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param $reportCount
     * @param $ndrId
     */
    public function __construct($id, $caseNumber, $firstName, $lastName, $email, $reportCount, $ndrId)
    {
        $this->id = $id;
        $this->caseNumber = $caseNumber;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->reportCount = $reportCount;
        $this->ndrId = $ndrId;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'case_number' => $this->caseNumber,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'email' => $this->email,
            'total_report_count' => $this->reportCount,
            'ndrId' => $this->ndrId
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getReportCount()
    {
        return $this->reportCount;
    }

    /**
     * @return mixed
     */
    public function getNdrId()
    {
        return $this->ndrId;
    }
}
