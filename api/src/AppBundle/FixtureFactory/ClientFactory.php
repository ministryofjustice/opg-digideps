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
            ->setFirstname('John')
            ->setLastname($data['id'] . '-client')
            ->setPhone('022222222222222')
            ->setAddress('Victoria road')
            ->setCourtDate(\DateTime::createFromFormat('Y-m-d', $courtDate));

        return $client;
    }
}
