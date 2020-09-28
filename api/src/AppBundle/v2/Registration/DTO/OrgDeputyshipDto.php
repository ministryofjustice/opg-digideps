<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\DTO;

class OrgDeputyshipDto
{
    /** @var string */
    private $email;
    private $deputyNumber;
    private $firstname;
    private $lastname;

    /**
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->getEmail());
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return OrgDeputyshipDto
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyNumber(): string
    {
        return $this->deputyNumber;
    }

    /**
     * @param string $deputyNumber
     * @return OrgDeputyshipDto
     */
    public function setDeputyNumber(string $deputyNumber): self
    {
        $this->deputyNumber = $deputyNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return OrgDeputyshipDto
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return OrgDeputyshipDto
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }
}
