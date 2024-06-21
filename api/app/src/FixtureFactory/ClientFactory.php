<?php

namespace App\FixtureFactory;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Organisation;
use Faker\Factory;

class ClientFactory
{
    public function create(array $data): Client
    {
        $client = new Client();
        $dateFormat = 'Y-m-d';

        $courtDate = isset($data['courtDate']) ? $data['courtDate'] : (new \DateTime())->format($dateFormat);

        $client
            ->setCaseNumber(isset($data['firstName']) ? $data['firstName'] : $data['id'])
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : 'John')
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : $data['id'].'-client')
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
    public function createGenericOrgClient(Deputy $deputy, Organisation $organisation, ?string $courtDate)
    {
        $faker = Factory::create();

        $client = (new Client())
            ->setCaseNumber($faker->unique()->randomNumber(8))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setPhone('0212112345')
            ->setAddress('1 Fake road')
            ->setAddress2($faker->city())
            ->setPostcode($faker->postcode())
            ->setAddress3('West Midlands')
            ->setCountry('GB')
            ->setCourtDate($courtDate ? new \DateTime($courtDate) : new \DateTime());

        $client->setDeputy($deputy);
        $client->setOrganisation($organisation);

        return $client;
    }
}
