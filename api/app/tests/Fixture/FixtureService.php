<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderKind;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Counter\Counter;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @phpstan-type Users array<string, User>
 * @phpstan-type Deputies array<string, Deputy>
 * @phpstan-type Organisations array<string, Organisation>
 * @phpstan-type Persons array{users: Users, deputies: Deputies, organisations: Organisations}
 * @phpstan-type Order array{order: CourtOrder, reports: array<Report>}
 * @phpstan-type OrderPair array<'pfa'|'hw', Order>
 */
final class FixtureService
{
    private readonly Counter $persistCounter;
    private Counter $counter;
    private readonly Generator $faker;
    private readonly string $password;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        private readonly int $flushAfter = 64,
    ) {
        $this->persistCounter = new Counter();
        $this->faker = Factory::create('en_GB');
        $this->password = $passwordHasher->hashPassword(new User(), 'DigidepsPass1234');
        $this->refreshCounter();
    }

    public function getId(): int
    {
        $this->refreshCounter();
        return $this->counter->nextInt();
    }

    public function getUid(): string
    {
        $this->refreshCounter();
        return $this->counter->nextString(8, '9');
    }

    /**
     * @template T of object
     * @param T $model
     * @return T
     */
    public function persist(object $model): object
    {
        $this->entityManager->persist($model);
        if ($this->persistCounter->nextInt() >= $this->flushAfter) {
            $this->flush();
        }
        return $model;
    }

    public function flush(): void
    {
        $this->entityManager->persist($this->counter);
        $this->entityManager->flush();
        $this->persistCounter->reset();
    }

    public function instantiateOnlyUser(UserType $userType, DeputyType $deputyType, ?string $emailDomain = null, ?Deputy $deputy = null, ?Organisation $organisation = null): User
    {
        $this->refreshCounter();
        $user = $this->persist($this->makeUser(new DeputyDescriptor('', $deputyType, $userType, emailDomain: $emailDomain), $deputy, $organisation));
        if ($userType === UserType::Deputy && $deputy === null) {
            $user->setDeputyUid((int)$this->counter->nextString(8, '9'));
            $this->persist($user);
        }
        $this->flush();
        return $user;
    }

    /**
     * @param Persons $persons
     * @return array{client: Client, orders: array<OrderPair>, persons: Persons}
     */
    public function instantiateScenario(
        Scenario $scenario,
        array &$persons = [
            'users' => [],
            'deputies' => [],
            'organisations' => [],
        ]
    ): array {
        $this->refreshCounter();

        $client = $this->persist($this->makeClient());
        /**
         * @var array<OrderPair> $orders
         */
        $orders = [];

        $current = $scenario;
        $first = true;
        while ($current !== null) {
            $pfa = $this->instantiateCourtOrder($client, $current, $first, CourtOrderType::PFA, $persons);
            $hw = $this->instantiateCourtOrder($client, $current, $first, CourtOrderType::HW, $persons, $pfa);

            $orders[] = array_filter([
                'pfa' => $pfa,
                'hw' => $hw
            ]);
            $current = $current->previous;
            $first = false;
        }
        $this->flush();

        return [
            'client' => $client,
            'orders' => $orders,
            'persons' => $persons,
        ];
    }

    /**
     * @param Persons $persons
     * @param Order|null $sibling
     * @return Order|null
     */
    private function instantiateCourtOrder(Client $client, Scenario $scenario, bool $first, CourtOrderType $type, array &$persons, ?array $sibling = null): ?array
    {
        $descriptor = $scenario->courtOrderDescriptor;
        if ($descriptor->single && (($type === CourtOrderType::HW && $descriptor->reportType !== CourtOrderReportType::OPG104) || ($type === CourtOrderType::PFA && $descriptor->reportType === CourtOrderReportType::OPG104))) {
            return null;
        }
        $deputySet = $sibling === null ? $descriptor->deputySet : $descriptor->siblingDeputySet ?? $scenario->courtOrderDescriptor->deputySet;

        $courtOrder = $this->persist($this->makeCourtOrder($descriptor, $client, $type));
        /**
         * @var null|User $primary;
         */
        $primary = null;

        foreach ($deputySet->descriptors as $deputyDescriptor) {
            $user = $persons['users'][$deputyDescriptor->deputyReference] ?? null;
            $deputy = $persons['deputies'][$deputyDescriptor->deputyReference] ?? null;
            $organisation = $persons['organisations'][$deputyDescriptor->emailDomain] ?? null;

            if ($deputyDescriptor->type !== DeputyType::LAY) {
                $organisation ??= $this->makeOrganisation($deputyDescriptor, $client);
            }

            if ($deputyDescriptor->userType !== UserType::Deputy) {
                $user ??= $this->makeUser($deputyDescriptor, organisation: $organisation);
            } else {
                $deputy ??= $this->makeDeputy($deputyDescriptor, $client, $organisation);
            }

            if ($deputy !== null) {
                $persons['deputies'][$deputyDescriptor->deputyReference] = $deputy;
                $deputy->associateWithCourtOrder($courtOrder);
                $user = $deputy->getUser();
                $this->persist($deputy);
            }
            if ($organisation !== null) {
                $persons['organisations'][$deputyDescriptor->emailDomain] = $organisation;
                $this->persist($organisation);
            }
            if ($user !== null) {
                $persons['users'][$deputyDescriptor->deputyReference] = $user;
                $user->addClient($client);
                $this->persist($user);
                if ($user->getIsPrimary()) {
                    $primary ??= $user;
                }
            }
        }
        $this->persist($client);
        $this->persist($courtOrder);
        $this->flush();
        $this->entityManager->refresh($courtOrder);

        /**
         * @var null|array<Report> $reports
         */
        $reports = null;

        if ($sibling !== null) {
            $sibling['order']->setSibling($courtOrder);
            $courtOrder->setSibling($sibling['order']);
            $this->persist($courtOrder);
            if ($courtOrder->getOrderKind() === CourtOrderKind::Hybrid) {
                $reports = $sibling['reports'];
            }
        }

        if ($reports === null) {
            $reports = [];
            if (!$descriptor->noReports) {
                for ($i = 0; $i <= $descriptor->submittedReports; $i++) {
                    $reports[] = $this->persist($this->makeReport($courtOrder, $reports[$i - 1] ?? null, $primary));
                }
                if (!$first) {
                    $this->makeReportSubmitted($reports[count($reports) - 1], $primary);
                }
            }
        }

        return [
            'order' => $this->persist($courtOrder),
            'reports' => $reports,
        ];
    }

    private function makeClient(): Client
    {
        return new Client()
            ->setCaseNumber($this->counter->nextString(size: 8, postfix: 'T'))
            ->setFirstname($this->faker->firstName())
            ->setLastname($this->faker->lastName())
            ->setAddress($this->faker->streetAddress())
            ->setAddress2('')
            ->setAddress3('')
            ->setAddress4('')
            ->setAddress5('')
            ->setPostcode($this->faker->postcode())
            ->setEmail(null)
            ->setOrganisation(null)
            ->setDateOfBirth(new \DateTime()->sub(new \DateInterval('P50Y')))
            ->setCountry('GB')
            ->setCourtDate(null)
            ->setDeletedAt(null)
            ->setArchivedAt(null)
            ->setCreatedAt(null);
    }


    private function makeCourtOrder(CourtOrderDescriptor $descriptor, Client $client, CourtOrderType $type): CourtOrder
    {
        $madeDate = new \DateTime()->sub(new \DateInterval('P6M'))->sub(new \DateInterval("P{$descriptor->submittedReports}Y"));
        $client->setCourtDate($madeDate);

        return $this->persist(new CourtOrder()
            ->setId($this->counter->nextInt())
            ->setCourtOrderUid($this->counter->nextString(8, prefix: 'C', postfix: 'C'))
            ->setClient($client)
            ->setOrderType($type)
            ->setOrderKind($descriptor->single ? CourtOrderKind::Single : ($descriptor->siblingDeputySet !== null ? CourtOrderKind::Hybrid : CourtOrderKind::Dual))
            ->setOrderReportType($descriptor->single || $type !== CourtOrderType::HW ? $descriptor->reportType : CourtOrderReportType::OPG104)
            ->setSibling(null)
            ->setOrderMadeDate($madeDate)
            ->setStatus($descriptor->active ? 'ACTIVE' : 'CLOSED')
            ->setCreatedAt(null)
            ->setUpdatedAt(null));
    }

    private function makeDeputy(DeputyDescriptor $descriptor, Client $client, ?Organisation $organisation): Deputy
    {
        $deputy = new Deputy()
            ->setId($this->counter->nextInt())
            ->setDeputyUid($this->counter->nextString(8, '9'))
            ->setDeputyType($descriptor->type)
            ->setFirstname($this->faker->firstName())
            ->setLastname($this->faker->lastName())
            ->setAddress1($this->faker->streetAddress())
            ->setAddress2('')
            ->setAddress3('')
            ->setAddress4('')
            ->setAddress5('')
            ->setAddressCountry(null)
            ->setAddressPostcode($this->faker->postcode())
            ->setPhoneMain($this->faker->phoneNumber())
            ->setPhoneAlternative($this->faker->phoneNumber());

        if ($descriptor->hasLogin) {
            $user = $this->makeUser($descriptor, $deputy, $organisation);
            $deputy->setUser($user);
            $client->getUsers()->add($user);
        } elseif ($organisation !== null) {
            $deputy->setOrganisation($organisation);
            $deputy->setEmail1("{$deputy->getFirstname()}.{$deputy->getLastname()}{$organisation->getEmailIdentifier()}");
        }
        $client->setDeputy($deputy);

        return $this->persist($deputy);
    }

    private function makeOrganisation(DeputyDescriptor $descriptor, Client $client): Organisation
    {
        $organisation = new Organisation()
            ->setId($this->counter->nextInt())
            ->setIsActivated(true)
            ->setDeletedAt(null)
            ->setName($descriptor->organisation);
        $organisation->setEmailIdentifier("@{$organisation->getId()}.{$descriptor->emailDomain}");

        $organisation->getClients()->add($client);

        return $this->persist($organisation);
    }

    private function makeUser(DeputyDescriptor $descriptor, ?Deputy $deputy = null, ?Organisation $organisation = null): User
    {
        $user = new User()
            ->setId($this->counter->nextInt())
            ->setDeputy($deputy)
            ->setDeputyUid((int)$deputy?->getDeputyUid() ?: null)
            ->setFirstname($deputy?->getFirstname() ?? $this->faker->firstName())
            ->setLastname($deputy?->getLastname() ?? $this->faker->lastName())
            ->setAddress1($deputy?->getAddress1() ?? $this->faker->streetAddress())
            ->setAddress2($deputy?->getAddress2() ?? '')
            ->setAddress3($deputy?->getAddress3() ?? '')
            ->setAddress4($deputy?->getAddress4() ?? '')
            ->setAddress5($deputy?->getAddress5() ?? '')
            ->setAddressCountry(null)
            ->setAddressPostcode($deputy?->getAddressPostcode() ?? $this->faker->postcode())
            ->setPhoneMain($deputy?->getPhoneMain() ?? $this->faker->phoneNumber())
            ->setRoleName(match ($descriptor->userType) {
                UserType::Deputy =>  match ($descriptor->type) {
                    DeputyType::LAY => User::ROLE_LAY_DEPUTY,
                    DeputyType::PRO => User::ROLE_PROF_NAMED,
                    DeputyType::PA => User::ROLE_PA_NAMED,
                },
                UserType::OrgAdmin => match ($descriptor->type) {
                    DeputyType::LAY => throw new \DomainException('A lay cannot be a org admin.'),
                    DeputyType::PRO => User::ROLE_PROF_ADMIN,
                    DeputyType::PA => User::ROLE_PA_ADMIN,
                },
                UserType::OrgTeamMember => match ($descriptor->type) {
                    DeputyType::LAY => throw new \DomainException('A lay cannot be a org admin.'),
                    DeputyType::PRO => User::ROLE_PROF_TEAM_MEMBER,
                    DeputyType::PA => User::ROLE_PA_TEAM_MEMBER,
                },
                UserType::Admin => User::ROLE_ADMIN,
                UserType::AdminManager => User::ROLE_ADMIN_MANAGER,
                UserType::SuperAdmin => User::ROLE_SUPER_ADMIN,
            })
            ->setActive($descriptor->isLoginActive)
            ->setIsPrimary($descriptor->isPrimary)
            ->setAgreeTermsUse(true)
            ->setPassword($this->password)
            ->setRegistrationDate(new \DateTime()->sub(new \DateInterval('P1Y')))
        ;

        if ($organisation !== null) {
            $user->setEmail("{$user->getFirstname()}.{$user->getLastname()}{$organisation->getEmailIdentifier()}");
            $organisation->addUser($user);
        } else {
            $user->setEmail("{$user->getFirstname()}.{$user->getLastname()}@{$user->getId()}.{$descriptor->emailDomain}");
        }

        $deputy?->setEmail1($user->getEmail());

        return $this->persist($user);
    }

    private function makeReport(CourtOrder $order, ?Report $previous, ?User $submitter): Report
    {
        $startDate = clone $order->getOrderMadeDate();
        if ($previous !== null) {
            $startDate = (clone $previous->getEndDate())->add(new \DateInterval('P1D'));
            $this->makeReportSubmitted($previous, $submitter);
        }
        $endDate = (clone $startDate)->add(new \DateInterval('P12M'))->sub(new \DateInterval('P1D'));
        $dueDate = (clone $endDate)->add(new \DateInterval('P1M'));
        $reportType = $order->getDesiredReportType();
        $report = new Report($order->getClient(), "{$reportType}", $startDate, $endDate, false)
            ->setId($this->counter->nextInt())
            ->setDueDate($dueDate)
            ->setSubmitted(false)
            ->setSubmitDate(null);
        $order->addReport($report);
        return $report;
    }

    private function makeReportSubmitted(Report $report, ?User $submitter): void
    {
        $report->setSubmitted(true);
        $report->setSubmitDate((clone $report->getEndDate())->add(new \DateInterval('P15D')));
        $report->setSubmittedBy($submitter);
        $this->persist(new ReportSubmission($report, $submitter));
        $this->persist($report);
    }

    private function refreshCounter(): void
    {
        $id = Counter::FIXTURE_ID;
        $counter = $this->entityManager->getRepository(Counter::class)->find($id);
        if ($counter === null) {
            $this->entityManager->getConnection()->executeQuery("
                INSERT INTO counter (id, counter)
                VALUES ({$id}, 0)
                ON CONFLICT DO NOTHING
            ");
            $this->entityManager->flush();
            $counter = $this->entityManager->getRepository(Counter::class)->find($id);
            if ($counter === null) {
                throw new \LogicException("We just inserted it!");
            }
        }
        $this->entityManager->persist($counter);
        $this->counter = $counter;
    }
}
