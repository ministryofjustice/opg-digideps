<?php

namespace Tests\OPG\Digideps\Backend\Integration;

use Doctrine\ORM\EntityRepository;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\CourtOrderDeputy;
use OPG\Digideps\Backend\Entity\Note;
use OPG\Digideps\Backend\Entity\Report\Asset;
use OPG\Digideps\Backend\Entity\Report\AssetOther;
use OPG\Digideps\Backend\Entity\Report\AssetProperty;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\VisitsCare;
use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\PreRegistration;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\DBAL\Connection;

/**
 * Used for unit testing.
 */
class Fixtures
{
    public function __construct(private EntityManager $em)
    {
    }

    public function getEntityManager(): EntityManager
    {
        return $this->em;
    }

    private static function getPGExportCommand(): string
    {
        $pgHost = getenv('PGHOST') ?: 'postgres';
        $pgPass = getenv('PGPASSWORD') ?: 'api';
        $pgUser = getenv('PGUSER') ?: 'api';

        return "export PGHOST={$pgHost}; export PGPASSWORD={$pgPass}; export PGDATABASE=digideps_unit_test; export PGUSER={$pgUser};";
    }

    public function createUser(
        ?string $email = null,
        ?string $roleName = null,
        ?\DateTime $registrationDate = null,
        ?string $phoneMain = null,
        ?int $deputyUid = null,
        ?bool $isPrimary = null,
    ): User {
        $user = new User(
            'name' . time(),
            'surname' . time(),
            'temp' . microtime(true) . rand(100, 99999) . '@temp.com'
        )
            ->setPassword('temp@temp.com');

        if ($email) {
            $user->setEmail($email);
        }
        if ($roleName) {
            $user->setRoleName($roleName);
        }
        if ($registrationDate) {
            $user->setRegistrationDate($registrationDate);
        }
        if ($phoneMain) {
            $user->setPhoneMain($phoneMain);
        }
        if ($deputyUid) {
            $user->setDeputyUid($deputyUid);
        }
        if ($isPrimary) {
            $user->setIsPrimary($isPrimary);
        }

        $this->em->persist($user);

        return $user;
    }

    public function createCoDeputyClient($users, array $settersMap = []): Client
    {
        $client = new Client();
        $client->setEmail('temp@temp.com');
        foreach ($settersMap as $k => $v) {
            $client->$k($v);
        }

        foreach ($users as $user) {
            $user->addClient($client);
        }

        $this->em->persist($client);

        return $client;
    }

    public function createDeputy(array $settersMap = [], ?User $user = null): Deputy
    {
        $deputy = new Deputy(
            (string)rand(100000, 999999),
            DeputyType::LAY,
            'name' . time(),
            'surname' . time(),
        );
        $deputy->setEmail1('temp' . microtime(true) . rand(100, 99999) . '@temp.com');
        $deputy->setOrganisation(null);

        foreach ($settersMap as $k => $v) {
            $deputy->$k($v);
        }

        if ($user !== null) {
            $deputy->setUser($user);
            $user->setDeputyUid(intval($deputy->getDeputyUid()));
            $this->em->persist($user);
        }

        $this->em->persist($deputy);

        return $deputy;
    }

    public function createClient(?User $user = null, array $settersMap = []): Client
    {
        // add clent, cot, report, needed for assets
        $client = new Client();
        $client->setEmail('temp@temp.com');
        foreach ($settersMap as $k => $v) {
            $client->$k($v);
        }

        if ($user) {
            $user->addClient($client);
        }

        $this->em->persist($client);

        return $client;
    }

    /**
     * @throws ORMException
     */
    public function createDocument($report, string $filename, bool $isReportPdf = true): Document
    {
        $doc = new Document($report, $filename);
        $doc->setIsReportPdf($isReportPdf);

        $this->em->persist($doc);

        return $doc;
    }

    /**
     * @throws ORMException
     */
    public function createReportSubmission(?Report $report = null, ?User $user = null): ReportSubmission
    {
        if (is_null($user)) {
            $user = $this->createUser(
                roleName: User::ROLE_LAY_DEPUTY,
                registrationDate: new \DateTime(),
                phoneMain: '01211234567'
            );
        }

        if (is_null($report)) {
            $client = $this->createClient($user);

            $report = $this->createReport($client);

            $other = new AssetOther($report)
                ->setValue((string)rand(1, 10000));

            $property = new AssetProperty($report)
                ->setValue((string)rand(1, 10000))
                ->setOwnedPercentage(rand(1, 100) / 100);

            $bankAccount = new BankAccount($report)
                ->setClosingBalance(floatval(rand(10, 1000000) / 10));

            $report->addAsset($other);
            $report->addAsset($property);
            $report->addAccount($bankAccount);
        }

        $submission = new ReportSubmission($report, $user);
        $report->setSubmitDate(new \DateTime('-2 days'));

        $this->em->persist($submission);
        $this->em->persist($report);

        return $submission;
    }

    public function createReport(
        Client $client,
        array $settersMap = [],
        ?CourtOrder $courtOrder = null
    ): Report {
        $validatedSettersMap = new ValidatingArray($settersMap);
        // should be created via ReportService, but this is a fixture, so better to keep it simple
        $report = new Report(
            $client,
            $validatedSettersMap->getStringOrDefault('setType', Report::LAY_PFA_HIGH_ASSETS_TYPE),
            $validatedSettersMap->getObjectOrNull('setStartDate', \DateTime::class) ?? new \DateTime('now'),
            $validatedSettersMap->getObjectOrNull('setEndDate', \DateTime::class) ?? new \DateTime('+12 months -1 day'),
        );

        if ($courtOrder !== null) {
            $courtOrder->addReport($report);
            $this->em->persist($courtOrder);
        }

        foreach ($settersMap as $k => $v) {
            $report->$k($v);
        }

        $this->em->persist($report);

        return $report;
    }

    public function createAccount(Report $report, array $settersMap = []): BankAccount
    {
        $ret = new BankAccount($report);
        $ret->setAccountNumber('1234')
            ->setBank('hsbc')
            ->setSortCode('101010');

        foreach ($settersMap as $k => $v) {
            $ret->$k($v);
        }

        $this->em->persist($ret);

        return $ret;
    }

    public function createContact(Report $report, array $settersMap = []): Contact
    {
        $contact = new Contact($report);
        $contact->setAddress('address' . time());

        foreach ($settersMap as $k => $v) {
            $contact->$k($v);
        }
        $this->em->persist($contact);

        return $contact;
    }

    public function createVisitsCare(Report $report, array $settersMap = []): VisitsCare
    {
        $sg = new VisitsCare($report);
        $sg->setDoYouLiveWithClient('yes');

        foreach ($settersMap as $k => $v) {
            $sg->$k($v);
        }
        $this->em->persist($sg);

        return $sg;
    }

    public function createAsset($type, Report $report, array $settersMap = []): Asset
    {
        $asset = Asset::factory($type, $report);

        foreach ($settersMap as $k => $v) {
            $asset->$k($v);
        }
        $this->em->persist($asset);

        return $asset;
    }

    public function createReportExpense($type, Report $report, array $settersMap = []): Expense
    {
        $record = new Expense($report, '');
        foreach ($settersMap as $k => $v) {
            $record->$k($v);
        }
        $this->em->persist($record);

        return $record;
    }

    public function createDecision(Report $report, array $settersMap = []): Decision
    {
        $decision = new Decision($report, true, 'description' . time());

        foreach ($settersMap as $k => $v) {
            $decision->$k($v);
        }
        $this->em->persist($decision);

        return $decision;
    }

    public function createNote(Client $client, User $createdBy, $cat, $title, $content): Note
    {
        $note = new Note($client, $cat, $title, $content);
        $note->setCreatedBy($createdBy);

        $this->em->persist($note);

        return $note;
    }

    /**
     * @return array<Organisation>
     */
    public function createOrganisations(int $amount, User ...$users): array
    {
        $orgs = [];
        for ($i = 1; $i <= $amount; ++$i) {
            $orgs[] = $this->createOrganisation(sprintf('Org %d', $i), sprintf(rand(1, 99999) . 'org_email_%d', $i), true, ...$users);
        }

        return $orgs;
    }

    /**
     * @throws ORMException
     */
    public function createOrganisation(string $name, string $identifier, bool $isActive, User ...$users): Organisation
    {
        $org = new Organisation($name, $identifier, $isActive);

        foreach ($users as $user) {
            $org->addUser($user);
        }

        $this->em->persist($org);

        return $org;
    }

    /**
     * @throws ORMException
     */
    public function addUserToOrganisation(int $userId, int $orgId): void
    {
        /** @var Organisation $org */
        $org = $this->em->getRepository(Organisation::class)->find($orgId);

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($userId);

        $org->addUser($user);
    }

    /**
     * @throws ORMException
     */
    public function addClientToOrganisation(int $clientId, int $orgId): void
    {
        /** @var Organisation $org */
        $org = $this->em->getRepository(Organisation::class)->find($orgId);

        /** @var Client $client */
        $client = $this->em->getRepository(Client::class)->find($clientId);

        $client->setOrganisation($org);
    }

    /**
     * @throws ORMException
     */
    public function deleteOrganisation(int $orgId): void
    {
        /** @var Organisation $org */
        $org = $this->em->getRepository(Organisation::class)->find($orgId);

        $org->setDeletedAt(new \DateTime('now'));
        $this->em->flush();
        $this->em->clear();
    }

    public function flush(object ...$entities): static
    {
        if (empty($entities)) {
            $this->em->flush();
        }

        foreach ($entities as $e) {
            $this->em->flush();
        }

        return $this;
    }

    public function remove(object ...$entities): static
    {
        foreach ($entities as $e) {
            $this->em->remove($e);
        }

        return $this;
    }

    public function persist(object $entity, object ...$entities): static
    {
        $this->em->persist($entity);
        foreach ($entities as $e) {
            $this->em->persist($e);
        }

        return $this;
    }

    public function clear(): static
    {
        $this->em->clear();

        return $this;
    }

    /**
     * @template T
     * @param class-string<T> $entity
     * @return EntityRepository<T>
     */
    public function getRepo(string $entity): EntityRepository
    {
        return $this->em->getRepository($entity);
    }

    public function getReportById(int $id): ?Report
    {
        return $this->getRepo(Report::class)->find($id);
    }

    public function getReportFreshSectionStatus(Report $report, string $section): array
    {
        return $this->getReportById($report->getId())->getStatus()->getSectionStateNotCached($section);
    }

    public function findUserByEmail(string $email): User
    {
        return $this->getRepo(User::class)->findOneBy(['email' => $email]);
    }

    public function getConnection(): Connection
    {
        return $this->em->getConnection();
    }

    private static function pgCommand($cmd): void
    {
        exec(self::getPGExportCommand() . $cmd);
    }

    public static function deleteReportsData($additionalTables = []): void
    {
        $tables = array_merge(['document', 'pre_registration', 'deputy_case', 'report_submission', 'report', 'satisfaction'], $additionalTables);
        self::pgCommand('PGOPTIONS=\'--client-min-messages=warning\' psql -c "truncate table ' . implode(',', $tables) . '  RESTART IDENTITY cascade";');
    }

    public function refresh(object $entity): void
    {
        $this->em->refresh($entity);
    }

    public function createCourtOrder(string $uid, CourtOrderType $type, CourtOrderKind $kind, string $status, \DateTime $madeDate = new \DateTime(), ?CourtOrderReportType $courtOrderReportType = null, ?Deputy $deputy = null, ?Client $client = null): CourtOrder
    {
        $courtOrder = new CourtOrder(
            $uid,
            $type,
            $courtOrderReportType ?? ($kind === CourtOrderKind::Hybrid || $type == CourtOrderType::PFA ? CourtOrderReportType::OPG102 : CourtOrderReportType::OPG104),
            $kind,
            $madeDate,
            $client ?? new Client(),
            $status
        );

        if ($deputy !== null) {
            $relationship = new CourtOrderDeputy()->setDeputy($deputy)->setCourtOrder($courtOrder)->setIsActive(true);
            $this->em->persist($relationship);
        }

        $this->em->persist($courtOrder);
        $this->em->persist($courtOrder->getClient());
        return $courtOrder;
    }

    public function deleteUser(int $id): void
    {
        $user = $this->getRepo(User::class)->find($id);
        if ($user !== null) {
            $this->em->remove($user);
        }
    }

    public function createPreRegistration(string $caseNumber, string $reportType, string $orderType, ?string $deputyUid = null, ?\DateTime $madeDate = null): PreRegistration
    {
        if (is_null($madeDate)) {
            $madeDate = new \DateTime();
        }

        $now = $madeDate->format('Y-m-d');

        $data = [
            'Case' => $caseNumber,
            'ReportType' => $reportType,
            'MadeDate' => $now,
            'OrderType' => $orderType,
        ];

        if (!is_null($deputyUid)) {
            $data['DeputyUid'] = $deputyUid;
        }

        return new PreRegistration($data);
    }
}
