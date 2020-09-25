<?php declare(strict_types=1);


namespace Tests\AppBundle\v2\Registration\Controller;

use Faker\Factory;
use Faker\Provider\en_GB\Address;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\AbstractTestController;

class OrgDeputyshipControllerTest extends AbstractTestController
{
    private static $tokenAdmin = null;
    private $headers = null;
    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
        }

        $this->headers = ['CONTENT_TYPE' => 'application/json', 'HTTP_AuthToken' => self::$tokenAdmin];
    }

    /**
     * @test
     * @dataProvider createProvider
     */
    public function create(string $orgDeputyshipJson, string $expectedContent)
    {
        $client = static::createClient(['environment' => 'test', 'debug' => false]);
        $client->request('POST', '/v2/org-deputyships', [], [], $this->headers, $orgDeputyshipJson);

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertEquals($expectedContent, $client->getResponse()->getContent());
    }

    public function createProvider()
    {
        return [
            '2 valid Org Deputyships' => [$this->generateOrgDeputyshipJson(2, 0), json_encode(['added' => 2, 'errors' => 0])],
            '1 valid, 1 invalid Org Deputyships' => [$this->generateOrgDeputyshipJson(1, 1), json_encode(['added' => 1, 'errors' => 1])]
        ];
    }

    private function generateOrgDeputyshipJson(int $validCount, int $invalidCount)
    {
        $deputyships = [];

        if ($validCount > 0) {
            foreach (range(1, $validCount) as $index) {
                $deputyships[] = $this->generateValidOrgDeputyshipArray();
            }
        }

        if ($invalidCount > 0) {
            foreach (range(1, $invalidCount) as $index) {
                $deputyships[] = $this->generateInvalidOrgDeputyshipArray();
            }
        }

        return json_encode($deputyships);
    }

    private function generateValidOrgDeputyshipArray()
    {
        $faker = Factory::create();

        return [
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
        ];
    }

    private function generateInvalidOrgDeputyshipArray()
    {
        $invalid = $this->generateValidOrgDeputyshipArray();
        $invalid['Email'] = '';

        return $invalid;
    }
}
