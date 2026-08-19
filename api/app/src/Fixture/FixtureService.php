<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\ClientBenefitsCheckInterface;
use OPG\Digideps\Backend\Entity\Counter\Counter;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\Report\Action;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\ClientBenefitsCheck;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\MentalCapacity;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\Report\VisitsCare;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        ParameterBagInterface $parameters,
        private readonly int $flushAfter = 256,
    ) {
        $this->persistCounter = new Counter();
        $this->faker = Factory::create('en_GB');
        $plain = ((array)$parameters->get('fixtures'))['account_password'] ?? null;
        if (!is_string($plain)) {
            throw new \DomainException("Fixtures password must be configured and be a string");
        }
        $this->password = $passwordHasher->hashPassword(new User('', '', ''), $plain);
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

    public function instantiateOnlyUser(UserType $userType, DeputyType $deputyType, ?string $emailDomain = null, ?Deputy $deputy = null, ?Organisation $organisation = null, bool $flush = true): User
    {
        $this->refreshCounter();
        $user = $this->makeUser(new DeputyDescriptor('', $deputyType, $userType, emailDomain: $emailDomain), $deputy, $organisation);
        if ($userType === UserType::Deputy && $deputy === null) {
            $user->setDeputyUid((int)$this->counter->nextString(8, '9'));
        }
        if ($flush) {
            $this->flush();
        }
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
        ],
        bool $flush = true,
    ): array {
        $this->refreshCounter();

        $client = $this->makeClient();
        /**
         * @var array<OrderPair> $orders
         */
        $orders = [];

        $current = $scenario;
        $first = true;
        while ($current !== null) {
            $pfa = $this->instantiateCourtOrder(
                $client,
                $current,
                $first,
                CourtOrderType::PFA,
                $persons
            );

            $hw = $this->instantiateCourtOrder(
                $client,
                $current,
                $first,
                CourtOrderType::HW,
                $persons,
                $pfa,
            );

            $orders[] = array_filter([
                'pfa' => $pfa,
                'hw' => $hw
            ]);
            $current = $current->previous;
            $first = false;
        }
        if ($flush) {
            $this->flush();
        }

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
    private function instantiateCourtOrder(
        Client $client,
        Scenario $scenario,
        bool $first,
        CourtOrderType $type,
        array &$persons,
        ?array $sibling = null,
    ): ?array {
        $descriptor = $scenario->courtOrderDescriptor;
        if ($descriptor->single && (($type === CourtOrderType::HW && $descriptor->reportType !== CourtOrderReportType::OPG104) || ($type === CourtOrderType::PFA && $descriptor->reportType === CourtOrderReportType::OPG104))) {
            return null;
        }
        $deputySet = $sibling === null ? $descriptor->deputySet : $descriptor->siblingDeputySet ?? $scenario->courtOrderDescriptor->deputySet;

        $courtOrder = $this->makeCourtOrder($descriptor, $client, $type);
        /**
         * @var null|User $primary;
         */
        $primary = null;

        foreach ($deputySet->descriptors as $deputyDescriptor) {
            $user = $persons['users'][$deputyDescriptor->deputyReference] ?? null;
            $deputy = $persons['deputies'][$deputyDescriptor->deputyReference] ?? null;
            $organisation = $persons['organisations'][$deputyDescriptor->emailDomain] ?? null;

            if ($deputyDescriptor->type !== DeputyType::LAY) {
                $organisation ??= $this->makeOrganisation($deputyDescriptor);
            }

            if ($deputyDescriptor->userType !== UserType::Deputy) {
                $user ??= $this->makeUser($deputyDescriptor, organisation: $organisation);
            } else {
                $deputy ??= $this->makeDeputy($deputyDescriptor, $organisation);
            }

            if ($deputy !== null) {
                $persons['deputies'][$deputyDescriptor->deputyReference] = $deputy;
                $deputy->associateWithCourtOrder($courtOrder, $deputyDescriptor->isActive);
                if ($organisation !== null) {
                    $deputy->setOrganisation($organisation);
                } else {
                    $client->setDeputy($deputy);
                }
                $user = $deputy->getUser();
                $this->persist($deputy);
            }
            if ($organisation !== null) {
                $persons['organisations'][$deputyDescriptor->emailDomain] = $organisation;
                $client->setOrganisation($organisation);
            }
            if ($user !== null) {
                $persons['users'][$deputyDescriptor->deputyReference] = $user;
                if ($user->isLayDeputy()) {
                    $user->addClient($client);
                }
                if ($user->getIsPrimary()) {
                    $primary ??= $user;
                }
            }
        }

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
            if ($courtOrder->getOrderKind() === CourtOrderKind::Hybrid) {
                $reports = $sibling['reports'];
                foreach ($reports as $report) {
                    $report->setCourtOrder($courtOrder);
                }
            }
        }

        if ($reports === null) {
            /** @var array<Report> $reports */
            $reports = [];
            $count = count($descriptor->reportList->reportDescriptors);
            foreach ($descriptor->reportList->reportDescriptors as $reportDescriptor) {
                $reports[] = $this->makeReport($courtOrder, $reportDescriptor);
                if (count($reports) !== $count || !$first) {
                    $this->makeReportSubmitted($reports[count($reports) - 1], $reportDescriptor, $primary);
                } elseif ($descriptor->reportList->currentIsSubmittable) {
                    $this->makeReportSubmittable($reports[count($reports) - 1]);
                }
            }
        }

        return [
            'order' => $courtOrder,
            'reports' => $reports,
        ];
    }

    private function makeClient(): Client
    {
        $address = explode("\n", $this->faker->streetAddress());
        $client = new Client()
            ->setCaseNumber($this->counter->nextString(size: 8, postfix: 'T'))
            ->setFirstname($this->faker->firstName())
            ->setLastname($this->faker->lastName())
            ->setAddress($address[0])
            ->setAddress2($address[1] ?? '')
            ->setAddress3($address[2] ?? '')
            ->setAddress4($address[3] ?? '')
            ->setAddress5($address[4] ?? '')
            ->setPostcode($this->faker->postcode())
            ->setEmail(null)
            ->setOrganisation(null)
            ->setDateOfBirth(new \DateTime()->sub(new \DateInterval('P50Y')))
            ->setCountry('GB')
            ->setCourtDate(null)
            ->setDeletedAt(null)
            ->setArchivedAt(null)
            ->setCreatedAt(null);

        return $this->persist($client);
    }

    private function makeCourtOrder(CourtOrderDescriptor $descriptor, Client $client, CourtOrderType $type): CourtOrder
    {
        $periods = max(0, count($descriptor->reportList->reportDescriptors) - 1);
        $madeDate = \DateTime::createFromImmutable($descriptor->madeDate ?? new \DateTimeImmutable()->sub(new \DateInterval('P6M'))->sub(new \DateInterval("P{$periods}Y")));

        $client->setCourtDate($madeDate);

        $courtOrder = new CourtOrder(
            $this->counter->nextString(8),
            $type,
            $descriptor->single || $type !== CourtOrderType::HW ? $descriptor->reportType : CourtOrderReportType::OPG104,
            $descriptor->single ? CourtOrderKind::Single : ($descriptor->siblingDeputySet === null ? CourtOrderKind::Hybrid : CourtOrderKind::Dual),
            $madeDate,
            $client,
            $descriptor->active ? 'ACTIVE' : 'CLOSED'
        )->setId($this->counter->nextInt());

        return $this->persist($courtOrder);
    }

    private function makeDeputy(DeputyDescriptor $descriptor, ?Organisation $organisation): Deputy
    {
        $address = explode("\n", $this->faker->streetAddress());
        $deputy = new Deputy(
            $this->counter->nextString(8, '9'),
            $descriptor->type,
            $this->faker->firstName(),
            $this->faker->lastName()
        )
            ->setId($this->counter->nextInt())
            ->setAddress1($address[0])
            ->setAddress2($address[1] ?? '')
            ->setAddress3($address[2] ?? '')
            ->setAddress4($address[3] ?? '')
            ->setAddress5($address[4] ?? '')
            ->setAddressCountry('GB')
            ->setAddressPostcode($this->faker->postcode())
            ->setPhoneMain($this->faker->phoneNumber())
            ->setPhoneAlternative($this->faker->phoneNumber());

        if ($descriptor->hasLogin) {
            $user = $this->makeUser($descriptor, $deputy, $organisation);
            $deputy->setUser($user);
        } elseif ($organisation !== null) {
            $deputy->setOrganisation($organisation);
            $deputy->setEmail1("{$deputy->getFirstname()}.{$deputy->getLastname()}{$organisation->getEmailIdentifier()}");
        }

        return $this->persist($deputy);
    }

    private function makeOrganisation(DeputyDescriptor $descriptor): Organisation
    {
        $id = $this->counter->nextInt();
        $emailIdentifier = "@{$id}.{$descriptor->emailDomain}";
        $organisation = new Organisation($descriptor->organisation, $emailIdentifier, true)->setId($id);

        return $this->persist($organisation);
    }

    private function makeUser(DeputyDescriptor $descriptor, ?Deputy $deputy = null, ?Organisation $organisation = null): User
    {
        $id = $this->counter->nextInt();
        $firstname = $deputy?->getFirstname() ?? $this->faker->firstName();
        $lastname = $deputy?->getLastname() ?? $this->faker->lastName();
        $address = explode("\n", $this->faker->streetAddress());

        if ($organisation !== null) {
            $email = "{$firstname}.{$lastname}{$organisation->getEmailIdentifier()}";
        } else {
            $email = "{$firstname}.{$lastname}@{$id}.{$descriptor->emailDomain}";
        }

        $user = new User(
            $firstname,
            $lastname,
            $email
        )
            ->setId($id)
            ->setDeputy($deputy)
            ->setDeputyUid((int)$deputy?->getDeputyUid() ?: null)
            ->setAddress1($deputy?->getAddress1() ?? $address[0])
            ->setAddress2($deputy?->getAddress2() ?? $address[1] ?? '')
            ->setAddress3($deputy?->getAddress3() ?? $address[2] ?? '')
            ->setAddress4($deputy?->getAddress4() ?? $address[3] ?? '')
            ->setAddress5($deputy?->getAddress5() ?? $address[4] ?? '')
            ->setAddressCountry($deputy?->getAddressCountry() ?? 'GB')
            ->setAddressPostcode($deputy?->getAddressPostcode() ?? $this->faker->postcode())
            ->setPhoneMain($deputy?->getPhoneMain() ?? $this->faker->phoneNumber())
            ->setRoleName(match ($descriptor->userType) {
                UserType::Deputy => match ($descriptor->type) {
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
            ->setCoDeputyClientConfirmed(true)
            ->setPassword($this->password)
            ->setRegistrationDate(new \DateTime()->sub(new \DateInterval('P1Y')))
            ->setLastLoggedIn(new \DateTime())
            ->setRegistrationRoute(User::SELF_REGISTER)
        ;

        $organisation?->addUser($user);
        $deputy?->setEmail1($user->getEmail());

        return $this->persist($user);
    }

    private function makeReport(CourtOrder $order, ReportDescriptor $reportDescriptor): Report
    {
        $reportType = $order->getDesiredReportType();
        $report = new Report(
            $order,
            "{$reportType}",
            \DateTime::createFromImmutable($reportDescriptor->startDate),
            \DateTime::createFromImmutable($reportDescriptor->endDate),
            false
        )
            ->setId($this->counter->nextInt())
            ->setDueDate(\DateTime::createFromImmutable($reportDescriptor->dueDate))
            ->setSubmitted(false)
            ->setSubmitDate(null);
        $order->addReport($report);
        foreach ($reportDescriptor->supportingDocumentsWithoutS3Objects as $supportingDocumentWithoutS3Object) {
            $this->addSupportingDocumentWithoutS3Object($report, $supportingDocumentWithoutS3Object);
        }

        return $this->persist($report);
    }

    private function makeReportSubmitted(Report $report, ReportDescriptor $reportDescriptor, ?User $submitter): void
    {
        $date = $reportDescriptor->submitDate ?? \DateTimeImmutable::createFromMutable($report->getEndDate())->add(new \DateInterval('P15D'));
        $report->setSubmitted(true);
        $report->setSubmitDate(\DateTime::createFromImmutable($date));
        $report->setSubmittedBy($submitter);

        $document = new Document($report, "DigiRep-{$this->counter->nextString(8)}.pdf");
        $document->setIsReportPdf(true);
        $document->setCreatedBy($submitter);
        $document->setStorageReference("dd_doc_{$report->getId()}_" . time());
        $this->persist($document);

        $reportSubmission = new ReportSubmission($report, $submitter);
        $reportSubmission->setUuid($this->counter->nextString(20));
        $reportSubmission->setCreatedOn(\DateTime::createFromImmutable($date));
        $this->persist($reportSubmission);
    }

    // this may be incomplete for making a HW report or a PFA 103 submittable,
    // but completes all necessary fields for a PFA 102
    private function makeReportSubmittable(Report $report): void
    {
        // decisions
        $report->setSignificantDecisionsMade('No')
            ->setReasonForNoDecisions('Nothing to be decided');

        $this->persist(new MentalCapacity($report)
            ->setMentalAssessmentDate(new \DateTime())
            ->setHasCapacityChanged(MentalCapacity::CAPACITY_STAYED_SAME));

        // contacts
        $report->setReasonForNoContacts('No contacts necessary');

        // visits and care
        $this->persist(new VisitsCare($report)
            ->setDoYouLiveWithClient('yes')
            ->setDoesClientReceivePaidCare('no')
            ->setWhoIsDoingTheCaring('family')
            ->setDoesClientHaveACarePlan('no'));

        // benefits check
        $this->persist(new ClientBenefitsCheck()
            ->setReport($report)
            ->setWhenLastCheckedEntitlement(ClientBenefitsCheckInterface::WHEN_CHECKED_IM_CURRENTLY_CHECKING)
            ->setDoOthersReceiveMoneyOnClientsBehalf('no'));

        // accounts
        $this->persist(new BankAccount($report)
            ->setBank('Test Account')
            ->setAccountType('current')
            ->setAccountNumber('0011')
            ->setOpeningBalance('100.00')
            ->setClosingBalance('100.00')
            ->setIsJointAccount('no'));

        // deputy expenses
        $report->setPaidForAnything('no');

        // gifts
        $report->setGiftsExist('no');

        // money in
        $report->setMoneyInExists('no')
            ->setReasonForNoMoneyIn('Nothing received');

        // money out
        $report->setMoneyOutExists('no')
            ->setReasonForNoMoneyOut('Nothing to pay for');

        // assets
        $report->setNoAssetToAdd(true);

        // debts
        $report->setHasDebts('no');

        // actions
        $this->persist(new Action($report)
            ->setDoYouExpectFinancialDecisions('no')
            ->setDoYouHaveConcerns('no'));

        // any other information
        $report->setActionMoreInfo('no');

        // supporting documents
        $report->setWishToProvideDocumentation('no');

        $this->flush();
        $this->entityManager->refresh($report);
        $report->updateSectionsStatusCache();
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

    private function addSupportingDocumentWithoutS3Object(Report $report, string $filename): void
    {
        // required so that the system recognises the report has documents
        $report->setWishToProvideDocumentation('yes');

        $document = new Document($report, $filename);
        $document->setIsReportPdf(false);
        $document->setCreatedBy($report->getSubmittedBy());
        $document->setStorageReference("dd_doc_{$report->getId()}_" . time());
        $this->persist($document);
    }
}
