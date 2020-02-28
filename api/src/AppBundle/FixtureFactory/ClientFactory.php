<?php

namespace AppBundle\FixtureFactory;

use AppBundle\Entity\Client;

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
            ->setCaseNumber($data['id'])
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
}
