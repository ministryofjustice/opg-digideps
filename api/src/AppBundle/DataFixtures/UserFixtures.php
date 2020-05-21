<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends AbstractDataFixture
{
    private $userData = [
        [
            'id' => '102',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
        ],
        [
            'id' => '103',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'L3',
        ],
        [
            'id' => '104',
            'deputyType' => 'LAY',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'A3',
        ],
        [
            'id' => '102-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'A2',
        ],
        [
            'id' => '104-6',
            'deputyType' => 'PA',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-6',
            'deputyType' => 'PA',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'P3',
        ],
        [
            'id' => '102-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'P2',
        ],
        [
            'id' => '104-5',
            'deputyType' => 'PROF',
            'reportType' => null,
            'reportVariation' => 'HW',
        ],
        [
            'id' => '102-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => '103-4-5',
            'deputyType' => 'PROF',
            'reportType' => 'OPG103',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'ndr',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'ndr' => true,
        ],
        [
            'id' => 'codep',
            'deputyType' => 'LAY',
            'reportType' => 'OPG102',
            'reportVariation' => 'L2',
            'codeputyEnabled' => true,
        ],
        [
            'id' => 'example1',
            'email' => 'jo.brown@example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'example2',
            'email' => 'bobby.blue@example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abc-ex1',
            'email' => 'john.smith@abc-solicitors.example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abc-ex2',
            'email' => 'kieth.willis@abc-solicitors.example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
        [
            'id' => 'abcd-ex3',
            'email' => 'marjorie.watkins@abcd-solicitors.example.com',
            'deputyType' => 'PROF',
            'reportType' => 'OPG102',
            'reportVariation' => 'HW',
        ],
//        [
//            'id' => 'leever-example',
//            'email' => 'main.contact@leever.example',
//            'deputyType' => 'PROF',
//            'reportType' => 'OPG102',
//            'reportVariation' => 'HW',
//        ],
    ];

    public function __construct(OrganisationRepository $orgRepository, OrganisationFactory $orgFactory)
    {
        $this->orgRepository = $orgRepository;
        $this->orgFactory = $orgFactory;
    }

    public function doLoad(ObjectManager $manager)
    {
        // Add users from array
        foreach ($this->userData as $data) {
            $this->addUser($data, $manager);
        }

        $manager->flush();
    }

    private function addUser($data, $manager) {
        // Create user
        $user = (new User())
            ->setFirstname(ucfirst($data['deputyType']) . ' Deputy ' . $data['id'])
            ->setLastname('User')
            ->setEmail(isset($data['email']) ? $data['email'] : 'behat-' . strtolower($data['deputyType']) .  '-deputy-' . $data['id'] . '@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(isset($data['ndr']))
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['deputyType'] === 'LAY' ? 'ROLE_LAY_DEPUTY' : 'ROLE_' . $data['deputyType'] . '_NAMED');

        $manager->persist($user);

        // Create CasRec record for lay deputies
        if ($data['deputyType'] === 'LAY') {
            $casRec = new CasRec([
                'Case' => $data['id'],
                'Surname' => $data['id'],
                'Deputy No' => str_replace('-', '', $data['id']),
                'Dep Surname' => 'User',
                'Dep Postcode' => 'SW1',
                'Typeofrep' => $data['reportType'],
                'Corref' => $data['reportVariation'],
                'OrderDate' => new \DateTime('2010-03-30')
            ]);
            $manager->persist($casRec);
        }

        // Create client
        $client = new Client();
        $client
            ->setCaseNumber($data['id'])
            ->setFirstname('John')
            ->setLastname($data['id'] . '-client')
            ->setPhone('022222222222222')
            ->setAddress('Victoria road')
            ->setCourtDate(\DateTime::createFromFormat('d/m/Y', '01/11/2017'));

        if ($data['deputyType'] === 'PROF' || $data['deputyType'] === 'PA') {
            $namedDeputy = new NamedDeputy();
            $namedDeputy
                ->setFirstname('Named')
                ->setLastname('Deputy ' . $data['id'])
                ->setDeputyNo('nd-' . $data['id'])
                ->setEmail1('behat-nd-' . $data['id'] . '@publicguardian.gov.uk')
                ->setPhoneMain('07911111111111')
                ->setAddress1('Victoria Road')
                ->setAddressPostcode('SW1')
                ->setAddressCountry('GB');

            $manager->persist($namedDeputy);

            $client->setNamedDeputy($namedDeputy);
        }

        $manager->persist($client);
        $user->addClient($client);

        if (!$client->getNdr()) {
            $ndr = new Ndr($client);
            $manager->persist($ndr);
        }

        // Create report for PROF/PA user 2 years ago
        if ($data['deputyType'] === 'PROF' || $data['deputyType'] === 'PA') {
            $realm = $data['deputyType'] === 'PROF' ? CasRec::REALM_PROF : CasRec::REALM_PA;
            $type = CasRec::getTypeBasedOnTypeofRepAndCorref($data['reportType'], $data['reportVariation'], $realm);
            $startDate = $client->getExpectedReportStartDate();
            $startDate->setDate('2016', intval($startDate->format('m')), intval($startDate->format('d')));
            $endDate = $client->getExpectedReportEndDate();
            $endDate->setDate('2017', intval($endDate->format('m')), intval($endDate->format('d')));

            $report = new Report($client, $type, $startDate, $endDate);

            $manager->persist($report);

            if (isset($data['email'])) {
                $organisation = $this->orgRepository->findByEmailIdentifier($data['email']);
                if (null === $organisation) {
                    $organisation = $this->orgFactory->createFromFullEmail($data['email'], $data['email']);
                    $manager->persist($organisation);
                    $manager->flush($organisation);
                }
            }
        }

        // If codeputy was enabled, add a secondary account
        if (isset($data['codeputyEnabled'])) {
            $user2 = clone $user;
            $user2->setLastname($user2->getLastname() . '-2');
            $user2->setEmail('behat-' . strtolower($data['deputyType']) .  '-deputy-' . $data['id'] . '-2@publicguardian.gov.uk');
            $user2->addClient($client);

            $manager->persist($user2);
        }
    }

    protected function getEnvironments()
    {
        return ['dev'];
    }
}
