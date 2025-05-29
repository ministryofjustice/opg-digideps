<?php

namespace App\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SelfRegisterData.
 */
class SelfRegisterData
{
    /**
     * Deputy's first name.
     *
     * @JMS\Type("string")
     */
    #[Assert\NotBlank(message: 'user.firstname.notBlank', groups: ['self_registration', 'verify_codeputy'])]
    #[Assert\Length(min: 2, max: 50, minMessage: 'user.firstname.minLength', maxMessage: 'user.firstname.maxLength', groups: ['self_registration', 'verify_codeputy'])]
    private ?string $firstname = null;

    /**
     * Deputy's last name.
     *
     * @JMS\Type("string")
     */
    #[Assert\NotBlank(message: 'user.lastname.notBlank', groups: ['self_registration', 'verify_codeputy'])]
    #[Assert\Length(min: 2, max: 50, minMessage: 'user.lastname.minLength', maxMessage: 'user.lastname.maxLength', groups: ['self_registration', 'verify_codeputy'])]
    private ?string $lastname = null;

    /**
     * Deputy's email.
     *
     * @JMS\Type("string")
     */
    #[Assert\NotBlank(message: 'user.email.notBlank', groups: ['self_registration', 'verify_codeputy'])]
    #[Assert\Email(message: 'user.email.invalid', groups: ['self_registration', 'verify_codeputy'])]
    #[Assert\Length(max: 60, maxMessage: 'user.email.maxLength', groups: ['self_registration', 'verify_codeputy'])]
    private ?string $email = null;

    /**
     * Deputy's postcode.
     *
     * @JMS\Type("string")
     */
    #[Assert\Length(max: 10, maxMessage: 'user.addressPostcode.maxLength', groups: ['self_registration', 'verify_codeputy'])]
    private ?string $postcode = null;

    /**
     * @JMS\Type("string")
     */
    #[Assert\NotBlank(message: 'client.firstname.notBlank', groups: ['self_registration'])]
    #[Assert\Length(min: 2, minMessage: 'client.firstname.minMessage', max: 50, maxMessage: 'client.firstname.maxMessage', groups: ['self_registration'])]
    private ?string $clientFirstname = null;

    /**
     * @JMS\Type("string")
     */
    #[Assert\NotBlank(message: 'client.lastname.notBlank', groups: ['self_registration', 'verify_codeputy'])]
    #[Assert\Length(min: 2, minMessage: 'client.lastname.minMessage', max: 50, maxMessage: 'client.lastname.maxMessage', groups: ['self_registration', 'verify_codeputy'])]
    private ?string $clientLastname = null;

    /**
     * @JMS\Type("string")
     */
    #[Assert\NotBlank(message: 'client.caseNumber.notBlank', groups: ['self_registration', 'verify_codeputy'])]
    #[Assert\Length(min: 8, max: 8, groups: ['self_registration', 'verify_codeputy'])]
    private ?string $caseNumber = null;

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = strtolower($email);
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): void
    {
        $this->postcode = $postcode;
    }

    public function getClientFirstname(): ?string
    {
        return $this->clientFirstname;
    }

    public function setClientFirstname(string $clientFirstname): void
    {
        $this->clientFirstname = $clientFirstname;
    }

    public function getClientLastname(): ?string
    {
        return $this->clientLastname;
    }

    public function setClientLastname(string $clientLastname): void
    {
        $this->clientLastname = $clientLastname;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): void
    {
        $this->caseNumber = $caseNumber;
    }

    /**
     * @return array<string, ?string>
     */
    public function toArray(): array
    {
        return [
            'deputy_firstname' => $this->firstname,
            'deputy_lastname' => $this->lastname,
            'deputy_email' => $this->email,
            'deputy_postcode' => $this->postcode,
            'client_lastname' => $this->clientLastname,
            'client_case_number' => $this->caseNumber,
        ];
    }

    /**
     * Function to replace known Unicode chars with equivalent ASCII
     * Used to better support matching user inputted data with data present in Pre-registration table.
     */
    public function replaceUnicodeChars(): void
    {
        $this->firstname = str_replace('’', '\'', $this->firstname ?? '');
        $this->lastname = str_replace('’', '\'', $this->lastname ?? '');
        $this->clientFirstname = str_replace('’', '\'', $this->clientFirstname ?? '');
        $this->clientLastname = str_replace('’', '\'', $this->clientLastname ?? '');
    }
}
