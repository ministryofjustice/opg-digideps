<?php

namespace App\FixtureFactory;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use Faker\Factory;

class ClientFactory
{
    /**
     * @param array $data
     * @return Client
     */
    public function create(array $data): Client
    {
        $client = new Client();

        $courtDate = isset($data['courtDate']) ? $data['courtDate'] : '2017-11-01';

        $client
            ->setCaseNumber(isset($data['firstName']) ? $data['firstName'] : $data['id'])
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : 'John')
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : $data['id'] . '-client')
            ->setPhone(isset($data['phone']) ? $data['phone'] : '022222222222222')
            ->setAddress(isset($data['address']) ? $data['address'] : 'Victoria road')
            ->setAddress2(isset($data['address2']) ? $data['address2'] : 'Birmingham')
            ->setPostcode(isset($data['postCode']) ? $data['postCode'] : 'B4 6HQ')
            ->setCounty(isset($data['county']) ? $data['county'] : 'West Midlands')
            ->setCountry('GB')
            ->setCourtDate(\DateTime::createFromFormat('Y-m-d', $courtDate));

        return $client;
    }

    /**
     * @param NamedDeputy $namedDeputy
     * @param Organisation $organisation
     * @return Client
     */
    public function createGenericOrgClient(NamedDeputy $namedDeputy, Organisation $organisation)
    {
        $faker = Factory::create();

        $client = (new Client())
            ->setCaseNumber($faker->unique()->randomNumber(8))
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setPhone('0212112345')
            ->setAddress('1 Fake road')
            ->setAddress2($faker->city)
            ->setPostcode($faker->postcode)
            ->setCounty('West Midlands')
            ->setCountry('GB')
            ->setCourtDate(\DateTime::createFromFormat('Y-m-d', $faker->date()));

        $client->setNamedDeputy($namedDeputy);
        $client->setOrganisation($organisation);

        return $client;
    }
}
