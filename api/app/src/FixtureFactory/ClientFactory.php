<?php

namespace OPG\Digideps\Backend\FixtureFactory;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;

class ClientFactory
{
    public function create(array $data): Client
    {
        $client = new Client();
        $dateFormat = 'Y-m-d';

        $courtDate = isset($data['courtDate']) ? $data['courtDate'] : new \DateTime()->format($dateFormat);

        $client
            ->setCaseNumber(isset($data['firstName']) ? $data['firstName'] : $data['id'])
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : 'John')
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : $data['id'] . '-client')
            ->setPhone(isset($data['phone']) ? $data['phone'] : '022222222222222')
            ->setAddress(isset($data['address']) ? $data['address'] : 'Victoria road')
            ->setAddress2(isset($data['address2']) ? $data['address2'] : 'Birmingham')
            ->setAddress3(isset($data['address3']) ? $data['address3'] : 'West Midlands')
            ->setPostcode(isset($data['postCode']) ? $data['postCode'] : 'B4 6HQ')
            ->setCountry('GB')
            ->setCourtDate(\DateTime::createFromFormat($dateFormat, $courtDate));

        return $client;
    }

    /**
     * @return Client
     */
    public function createGenericOrgClient(Deputy $deputy, Organisation $organisation, ?string $courtDate): Client
    {
        $client = new Client()
            ->setCaseNumber(self::createValidCaseNumber())
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setPhone('0212112345')
            ->setAddress('1 Fake road')
            ->setAddress2('Birmingham')
            ->setPostcode('B1 1AA')
            ->setAddress3('West Midlands')
            ->setCountry('GB')
            ->setCourtDate($courtDate ? new \DateTime($courtDate) : new \DateTime());

        $client->setDeputy($deputy);
        $client->setOrganisation($organisation);

        return $client;
    }

    /**
     * Sirius has a modulus 11 validation check on case references (because casrec.) which we should adhere to
     * to make sure integration tests create data that is in the correct format.
     */
    private static function createValidCaseNumber(): string
    {
        $ref = '';
        $sum = 0;

        foreach ([3, 4, 7, 5, 8, 2, 4] as $constant) {
            $value = mt_rand(0, 9);
            $ref .= $value;
            $sum += $value * $constant;
        }

        $checkbit = (11 - ($sum % 11)) % 11;

        if (10 === $checkbit) {
            $checkbit = 'T';
        }

        return $ref . $checkbit;
    }
}
