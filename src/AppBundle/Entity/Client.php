<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Report\Report;
use DateTime;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

class Client
{
    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"edit", "pa-edit"})

     * @var int
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * Lay + PA
     * @Assert\NotBlank( message="client.firstname.notBlank", groups={"lay-deputy-client", "pa-client"})
     * @Assert\Length(min=2, minMessage= "client.firstname.minMessage", max=50, maxMessage= "client.firstname.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $firstname;


    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.lastname.notBlank", groups={"lay-deputy-client", "pa-client"})
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage", max=50, maxMessage= "client.lastname.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $lastname;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $users;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Report>")
     *
     * @var array
     */
    private $reports;

    /**
     * @JMS\Type("AppBundle\Entity\Report\Report")
     *
     * @var array
     */
    private $reportCurrent;

    /**
     * @var Odr\Odr
     *
     * @JMS\Type("AppBundle\Entity\Odr\Odr")
     */
    private $odr;


    /**
     * @JMS\Exclude()
     *
     * @var string
     */
    private $fullname;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit"})
     *
     * @Assert\NotBlank( message="client.caseNumber.notBlank", groups={"lay-deputy-client"})
     * @Assert\Length(min = 8, max=8, exactMessage= "client.caseNumber.exactMessage1", groups={"lay-deputy-client"})
     * @Assert\Length(min = 8, max=8, exactMessage= "client.caseNumber.exactMessage2", groups={"lay-deputy-client"})
     *
     * @var string
     */
    private $caseNumber;

    /**
     * @JMS\Accessor(setter="setCourtDateWithoutTime")
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"edit"})
     *
     * @Assert\NotBlank( message="client.courtDate.notBlank", groups={"lay-deputy-client"})
     * @Assert\Date( message="client.courtDate.message", groups={"lay-deputy-client"})
     * @Assert\LessThan("today", groups={"pa-client"}, message="client.courtDate.lessThan", groups={"lay-deputy-client"})
     *
     * @var DateTime
     */
    private $courtDate;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.address.notBlank", groups={"lay-deputy-client"})
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $address;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(max=200, maxMessage="client.address.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(max=75, maxMessage="client.county.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $county;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\NotBlank( message="client.postcode.notBlank", groups={"lay-deputy-client"})
     * @Assert\Length(max=10, maxMessage= "client.postcode.maxMessage", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $postcode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit"})
     *
     * @var string
     */
    private $country;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"edit", "pa-edit"})
     *
     * @Assert\Length(min=10, max=20, minMessage="common.genericPhone.minLength", maxMessage="common.genericPhone.maxLength", groups={"lay-deputy-client", "pa-client"})
     *
     * @var string
     */
    private $phone;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"pa-edit"})
     *
     * @Assert\Email( message="client.email.invalid", checkMX=false, checkHost=false, groups={"pa-client"})
     * @Assert\Length(max=60, maxMessage="client.email.maxLength", groups={"pa-client"})
     *
     * @var string
     */
    private $email;

    /**
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"pa-edit"})
     *
     * @Assert\LessThan("today", groups={"pa-client"}, message="client.dateOfBirth.lessThan")
     *
     * @var DateTime
     */
    private $dateOfBirth;

    public function __construct()
    {
        $this->users = [];
        $this->reports = [];
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    public function addUser($user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * @return array $reports
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param int $id report ID
     *
     * @return Report|null
     */
    public function getReportById($id)
    {
        foreach ($this->reports as $report) {
            if ($report->getId() == $id) {
                return $report;
            }
        }

        return;
    }

    /**
     * @param  $report
     *
     * @return \AppBundle\Entity\Client
     */
    public function addReport($report)
    {
        $this->reports[] = $report;

        return $this;
    }

    /**
     * @param Report[] $reports
     *
     * @return \AppBundle\Entity\Client
     */
    public function setReports($reports)
    {
        $this->reports = $reports;

        return $this;
    }

    /**
     * @return Report
     */
    public function getReportCurrent()
    {
        return $this->reportCurrent;
    }

    /**
     * @param mixed $reportCurrent
     */
    public function setReportCurrent($reportCurrent)
    {
        $this->reportCurrent = $reportCurrent;
    }


    /**
     * @return Odr\Odr
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param Odr\Odr $odr
     */
    public function setOdr($odr)
    {
        $this->odr = $odr;

        return $this;
    }

    public function removeReport($report)
    {
        if (!empty($this->reports)) {
            foreach ($this->reports as $key => $reportObj) {
                if ($reportObj->getId() == $report->getId()) {
                    unset($this->reports[$key]);

                    return $this;
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasDetails()
    {
        if (!empty($this->getAddress())) {
            return true;
        }
    }

    public function hasReport()
    {
        if (!empty($this->reports)) {
            return true;
        }

        return false;
    }

    public function getFullname()
    {
        $this->fullname = $this->firstname . ' ' . $this->lastname;

        return $this->fullname;
    }

    public function setCourtDateWithoutTime($courtDate = null)
    {
        $this->courtDate = ($courtDate instanceof DateTime) ?
                new DateTime($courtDate->format('Y-m-d')) : null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Client
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return Client
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return Client
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     *
     * @return Client
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return array
     */
    public function getCourtDate()
    {
        return $this->courtDate;
    }

    /**
     * @param array $courtDate
     *
     * @return Client
     */
    public function setCourtDate($courtDate)
    {
        $this->courtDate = $courtDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return Client
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     *
     * @return Client
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * @param string $county
     *
     * @return Client
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     *
     * @return Client
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return Client
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return array
     */
    public function getAddressNotEmptyParts()
    {
        return array_filter([
            $this->address,
            $this->address2,
            $this->county,
            $this->postcode,
        ]);
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return Client
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return DateTime $dateOfBirth
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param DateTime $dateOfBirth
     *
     * @return \AppBundle\Entity\User
     */
    public function setDateOfBirth(DateTime $dateOfBirth = null)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /*
     * @return int
     */
    public function getAge()
    {
        if (!$this->dateOfBirth) {
            return;
        }
        $to = new DateTime('today');
        return $this->dateOfBirth->diff($to)->y;
    }
}
