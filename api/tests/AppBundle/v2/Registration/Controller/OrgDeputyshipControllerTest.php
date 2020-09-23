<?php declare(strict_types=1);


namespace Tests\AppBundle\v2\Registration\Controller;

use Faker\Factory;
use Faker\Provider\en_GB\Address;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OrgDeputyshipControllerTest extends WebTestCase
{
    /** @test */
    public function create()
    {
        $client = static::createClient();
        $json = $this->generateOrgCsvJson();
        $client->request('POST', '/org-deputyship/create', [], [], [], $this->generateOrgCsvJson());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    private function generateOrgCsvJson()
    {
        $faker = Factory::create();

        return json_encode([
            'Email'        => $faker->email,
            'Deputy No'    => $faker->randomNumber(8),
            'Dep Postcode' => Address::postcode(),
            'Dep Forename' => $faker->firstName,
            'Dep Surname'  => $faker->lastName,
            'Dep Type'     => $faker->randomElement([21,22,23,24,25,26,27,29,50,63]),
            'Dep Adrs1'    => $faker->buildingNumber . ' ' . $faker->streetName,
            'Dep Adrs2'    => Address::cityPrefix() . ' ' . $faker->city,
            'Dep Adrs3'    => $faker->city,
            'Dep Adrs4'    => Address::county(),
            'Dep Adrs5'    => 'UK',
            'Case'       => (string) $faker->randomNumber(8),
            'Forename'   => $faker->firstName,
            'Surname'    => $faker->lastName,
            'Corref'     => 'A3',
            'Report Due' => $faker->dateTimeThisYear->format('d-M-Y'),
        ]);
    }
}
