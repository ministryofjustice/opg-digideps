<?php

namespace App\Tests\Integration;

use App\Entity as EntityDir;
use App\Entity\Client;
use App\Entity\CourtOrder;
use App\Entity\Deputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

/**
 * Used for unit testing.
 */
class Fixtures
{
    public const PG_DUMP_PATH = '/tmp/dd_phpunit.pgdump';

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getEntityManager()
    {
        return $this->em;
    }

    public function getQueryResults($dql)
    {
        return $this->em->createQuery($dql)->getResult();
    }

    /**
     * @return string
     **/
    private static function getPGExportCommand()
    {
        $pgHost = getenv('PGHOST') ?: 'postgres';
        $pgPass = getenv('PGPASSWORD') ?: 'api';
        $pgUser = getenv('PGUSER') ?: 'api';

        return "export PGHOST={$pgHost}; export PGPASSWORD={$pgPass}; export PGDATABASE=digideps_unit_test; export PGUSER={$pgUser};";
    }

    /**
     * @return User
     */
    public function createUser(array $settersMap = [])
    {
        // add clent, cot, report, needed for assets
        $user = new User();
        $user->setEmail('temp'.microtime(true).rand(100, 99999).'@temp.com');
        $user->setPassword('temp@temp.com');
        $user->setFirstname('name'.time());
        $user->setLastname('surname'.time());

        foreach ($settersMap as $k => $v) {
            $user->$k($v);
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

    /**
     * @return Deputy
     */
    public function createDeputy(array $settersMap = [])
    {
        $deputy = new Deputy();
        $deputy->setDeputyUid('UID'.rand(1, 999999));
        $deputy->setEmail1('temp'.microtime(true).rand(100, 99999).'@temp.com');
        $deputy->setFirstname('name'.time());
        $deputy->setLastname('surname'.time());

        foreach ($settersMap as $k => $v) {
            $deputy->$k($v);
        }

        $this->em->persist($deputy);

        return $deputy;
    }

    /**
     * @return Client
     */
    public function createClient(?User $user = null, array $settersMap = [])
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
     * @return EntityDir\Ndr\Ndr
     */
    public function createNdr(Client $client, array $settersMap = [])
    {
        $ndr = new EntityDir\Ndr\Ndr($client);

        foreach ($settersMap as $k => $v) {
            $ndr->$k($v);
        }

        $this->em->persist($ndr);

        return $ndr;
    }

    /**
     * @return EntityDir\Report\Document
     *
     * @throws ORMException
     */
    public function createDocument($report, string $filename, bool $isReportPdf = true)
    {
        $doc = new EntityDir\Report\Document($report);
        $doc->setFileName($filename);
        $doc->setIsReportPdf($isReportPdf);

        $this->em->persist($doc);

        return $doc;
    }

    public function createChecklist(Report $report)
    {
        $cl = new EntityDir\Report\Checklist($report);
        $this->em->persist($cl);

        return $cl;
    }

    /**
     * @return ReportSubmission
     *
     * @throws ORMException
     */
    public function createReportSubmission(?Report $report = null, ?User $user = null)
    {
        if (is_null($user)) {
            $user = $this->createUser(['setRoleName' => User::ROLE_LAY_DEPUTY, 'setRegistrationDate' => new \DateTime(), 'setPhoneMain' => '01211234567']);
        }

        if (is_null($report)) {
            $client = $this->createClient($user);

            $report = $this->createReport($client);

            $other = (new EntityDir\Report\AssetOther())
                ->setValue(rand(1, 10000))
                ->setReport($report);

            $property = (new EntityDir\Report\AssetProperty())
                ->setValue(rand(1, 10000))
                ->setOwnedPercentage(rand(1, 100) / 100)
                ->setReport($report);

            $bankAccount = (new EntityDir\Report\BankAccount())
                ->setClosingBalance(floatval(rand(10, 1000000) / 10))
                ->setReport($report);

            $report->addAsset($other);
            $report->addAsset($property);
            $report->addAccount($bankAccount);

            $ndr = $this->createNdr($client);
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
    ) {
        // should be created via ReportService, but this is a fixture, so better to keep it simple
        $report = new Report(
            $client,
            empty($settersMap['setType']) ? Report::LAY_PFA_HIGH_ASSETS_TYPE : $settersMap['setType'],
            empty($settersMap['setStartDate']) ? new \DateTime('now') : $settersMap['setStartDate'],
            empty($settersMap['setEndDate']) ? new \DateTime('+12 months -1 day') : $settersMap['setEndDate']
        );

        foreach ($settersMap as $k => $v) {
            $report->$k($v);
        }

        $this->em->persist($report);

        return $report;
    }

    /**
     * @return EntityDir\Report\BankAccount
     */
    public function createAccount(Report $report, array $settersMap = [])
    {
        $ret = new EntityDir\Report\BankAccount();
        $ret->setReport($report);
        $ret->setAccountNumber('1234')
            ->setBank('hsbc')
            ->setSortCode('101010');

        foreach ($settersMap as $k => $v) {
            $ret->$k($v);
        }

        $this->em->persist($ret);

        return $ret;
    }

    /**
     * @return EntityDir\Ndr\BankAccount
     */
    public function createNdrAccount(EntityDir\Ndr\Ndr $ndr, array $settersMap = [])
    {
        $ret = new EntityDir\Ndr\BankAccount();
        $ret->setNdr($ndr);
        $ret->setAccountNumber('1234')
            ->setBank('hsbc')
            ->setSortCode('101010');

        foreach ($settersMap as $k => $v) {
            $ret->$k($v);
        }

        $this->em->persist($ret);

        return $ret;
    }

    /**
     * @return EntityDir\Report\Contact
     */
    public function createContact(Report $report, array $settersMap = [])
    {
        $contact = new EntityDir\Report\Contact();
        $contact->setReport($report);
        $contact->setAddress('address'.time());

        foreach ($settersMap as $k => $v) {
            $contact->$k($v);
        }
        $this->em->persist($contact);

        return $contact;
    }

    /**
     * @return EntityDir\Report\VisitsCare
     */
    public function createVisitsCare(Report $report, array $settersMap = [])
    {
        $sg = new EntityDir\Report\VisitsCare();
        $sg->setReport($report);
        $sg->setDoYouLiveWithClient('yes');

        foreach ($settersMap as $k => $v) {
            $sg->$k($v);
        }
        $this->em->persist($sg);

        return $sg;
    }

    /**
     * @return EntityDir\Ndr\VisitsCare
     */
    public function createNdrVisitsCare(EntityDir\Ndr\Ndr $ndr, array $settersMap = [])
    {
        $vc = new EntityDir\Ndr\VisitsCare();
        $vc->setNdr($ndr);
        $vc->setDoYouLiveWithClient('yes');

        foreach ($settersMap as $k => $v) {
            $vc->$k($v);
        }
        $this->em->persist($vc);

        return $vc;
    }

    /**
     * @return EntityDir\Report\Asset
     */
    public function createAsset($type, Report $report, array $settersMap = [])
    {
        $asset = EntityDir\Report\Asset::factory($type);
        $asset->setReport($report);

        foreach ($settersMap as $k => $v) {
            $asset->$k($v);
        }
        $this->em->persist($asset);

        return $asset;
    }

    /**
     * @return EntityDir\Ndr\Asset
     */
    public function createNdrAsset($type, EntityDir\Ndr\Ndr $ndr, array $settersMap = [])
    {
        $asset = EntityDir\Ndr\Asset::factory($type);
        $asset->setNdr($ndr);

        foreach ($settersMap as $k => $v) {
            $asset->$k($v);
        }
        $this->em->persist($asset);

        return $asset;
    }

    /**
     * @return EntityDir\Ndr\Expense
     */
    public function createNdrExpense($type, EntityDir\Ndr\Ndr $ndr, array $settersMap = [])
    {
        $record = new EntityDir\Ndr\Expense($ndr);
        foreach ($settersMap as $k => $v) {
            $record->$k($v);
        }
        $this->em->persist($record);

        return $record;
    }

    /**
     * @return EntityDir\Report\Expense
     */
    public function createReportExpense($type, Report $report, array $settersMap = [])
    {
        $record = new EntityDir\Report\Expense($report);
        foreach ($settersMap as $k => $v) {
            $record->$k($v);
        }
        $this->em->persist($record);

        return $record;
    }

    /**
     * @return EntityDir\Report\Decision
     */
    public function createDecision(Report $report, array $settersMap = [])
    {
        $decision = new EntityDir\Report\Decision();
        $decision->setReport($report);
        $decision->setClientInvolvedBoolean(true);
        $decision->setDescription('description'.time());

        foreach ($settersMap as $k => $v) {
            $decision->$k($v);
        }
        $this->em->persist($decision);

        return $decision;
    }

    /**
     * @return EntityDir\Note
     */
    public function createNote(Client $client, User $createdBy, $cat, $title, $content)
    {
        $note = new EntityDir\Note($client, $cat, $title, $content);
        $note->setCreatedBy($createdBy);

        $this->em->persist($note);

        return $note;
    }

    /**
     * @throws ORMException
     */
    public function createOrganisations(int $amount): array
    {
        $orgs = [];
        for ($i = 1; $i <= $amount; ++$i) {
            $orgs[] = $this->createOrganisation(sprintf('Org %d', $i), sprintf(rand(1, 99999).'org_email_%d', $i), true);
        }

        return $orgs;
    }

    /**
     * @throws ORMException
     */
    public function createOrganisation(string $name, string $identifier, bool $isActive): Organisation
    {
        $org = new Organisation();
        $org->setName($name);
        $org->setEmailIdentifier($identifier);
        $org->setIsActivated($isActive);

        $this->em->persist($org);

        return $org;
    }

    /**
     * @throws ORMException
     */
    public function addUserToOrganisation(int $userId, int $orgId)
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
    }

    public function flush()
    {
        $args = func_get_args();
        if (empty($args)) {
            $this->em->flush();
        }

        foreach ($args as $e) {
            $this->em->flush($e);
        }

        return $this;
    }

    public function remove()
    {
        $args = func_get_args();
        foreach ($args as $e) {
            $this->em->remove($e);
        }

        return $this;
    }

    public function persist()
    {
        $args = func_get_args();
        if (empty($args)) {
            throw new \InvalidArgumentException('You must pass at least one object to persist');
        }
        foreach (func_get_args() as $e) {
            $this->em->persist($e);
        }

        return $this;
    }

    public function clear()
    {
        $this->em->clear();

        return $this;
    }

    public function getRepo($entity)
    {
        return $this->em->getRepository(class_exists($entity) ? $entity : "App\\Entity\\{$entity}");
    }

    /**
     * @return Report
     */
    public function getReportById(int $id)
    {
        return $this->getRepo('Report\Report')->find($id);
    }

    /**
     * @return array
     */
    public function getReportFreshSectionStatus(Report $report, string $section)
    {
        return $this->getReportById($report->getId())->getStatus()->getSectionStateNotCached($section);
    }

    /**
     * @return User
     */
    public function findUserByEmail(string $email)
    {
        return $this->getRepo('User')->findOneBy(['email' => $email]);
    }

    /**
     * @param string $deputyNo
     *
     * @return Deputy
     */
    public function findDeputyByNumber($deputyNo)
    {
        return $this->getRepo('Deputy')->findOneBy(['deputyNo' => $deputyNo]);
    }

    public function getConnection()
    {
        return $this->em->getConnection();
    }

    private static function pgCommand($cmd)
    {
        exec(self::getPGExportCommand().$cmd);
    }

    public static function initDb()
    {
    }

    public static function backupDb()
    {
        self::pgCommand('pg_dump --clean > '.self::PG_DUMP_PATH);
    }

    public static function restoreDb()
    {
        if (!file_exists(self::PG_DUMP_PATH)) {
            throw new \RuntimeException(self::PG_DUMP_PATH.' not found');
        }
        self::pgCommand('psql < '.self::PG_DUMP_PATH);
    }

    public static function deleteReportsData($additionalTables = [])
    {
        $tables = array_merge(['document', 'pre_registration', 'deputy_case', 'report_submission', 'report', 'odr', 'satisfaction'], $additionalTables);
        self::pgCommand('PGOPTIONS=\'--client-min-messages=warning\' psql -c "truncate table '.implode(',', $tables).'  RESTART IDENTITY cascade";');
    }

    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }

    public function createUserResearchResponse(int $howMany)
    {
        $range = range(1, $howMany);

        foreach ($range as $i) {
            $rs = $this->createReportSubmission();

            $researchType = new EntityDir\UserResearch\ResearchType(['surveys']);

            $userResearchResponse = (new EntityDir\UserResearch\UserResearchResponse())
                ->setCreated(new \DateTime())
                ->setDeputyshipLength('oneToFive')
                ->setUser($rs->getCreatedBy())
                ->setHasAccessToVideoCallDevice(true)
                ->setResearchType($researchType);

            $satisfaction = (new EntityDir\Satisfaction())
                ->setReport($rs->getReport())
                ->setDeputyrole(User::ROLE_LAY_DEPUTY)
                ->setCreated(new \DateTime())
                ->setComments(' Some comments')
                ->setScore(rand(1, 5))
                ->setReporttype(Report::LAY_COMBINED_LOW_ASSETS_TYPE)
                ->setUserResearchResponse($userResearchResponse);

            $userResearchResponse->setSatisfaction($satisfaction);

            $this->em->persist($userResearchResponse);
            $this->em->persist($satisfaction);
            $this->em->persist($researchType);

            if (100 === $i) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();
    }

    public function createCourtOrder(int $uid, string $type, string $status, \DateTime $madeDate = new \DateTime()): CourtOrder
    {
        $courtOrder = new CourtOrder();
        $courtOrder->setCourtOrderUid($uid);
        $courtOrder->setOrderType($type);
        $courtOrder->setStatus($status);
        $courtOrder->setOrderMadeDate($madeDate);

        return $courtOrder;
    }
}
