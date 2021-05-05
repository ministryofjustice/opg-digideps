<?php

namespace Tests\Unit;

use App\Entity as EntityDir;
use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Used for unit testing.
 */
class Fixtures
{
    const PG_DUMP_PATH = '/tmp/dd_phpunit.pgdump';

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
     * @return EntityDir\User
     */
    public function createUser(array $settersMap = [])
    {
        // add clent, cot, report, needed for assets
        $user = new EntityDir\User();
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

    /**
     * @return EntityDir\Client
     */
    public function createClient(EntityDir\User $user, array $settersMap = [])
    {
        // add clent, cot, report, needed for assets
        $client = new EntityDir\Client();
        $client->setEmail('temp@temp.com');
        foreach ($settersMap as $k => $v) {
            $client->$k($v);
        }

        $user->addClient($client);

        $this->em->persist($client);

        return $client;
    }

    /**
     * @return EntityDir\Ndr\Ndr
     */
    public function createNdr(EntityDir\Client $client, array $settersMap = [])
    {
        $ndr = new EntityDir\Ndr\Ndr($client);

        foreach ($settersMap as $k => $v) {
            $ndr->$k($v);
        }

        $this->em->persist($ndr);

        return $ndr;
    }

    /**
     * @param mixed $report
     *
     * @return EntityDir\Report\Document
     *
     * @throws \Doctrine\ORM\ORMException
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
     * @throws \Doctrine\ORM\ORMException
     */
    public function createReportSubmission(Report $report = null, User $user = null)
    {
        if (is_null($user)) {
            $user = $this->createUser();
        }

        if (is_null($report)) {
            $report = $this->createReport($this->createClient($user));
        }

        $submission = new ReportSubmission($report, $user);

        $this->em->persist($submission);

        return $submission;
    }

    public function createReport(
        EntityDir\Client $client,
        array $settersMap = []
    ) {
        //should be created via ReportService, but this is a fixture, so better to keep it simple
        $report = new EntityDir\Report\Report(
            $client,
            empty($settersMap['setType']) ? EntityDir\Report\Report::TYPE_102 : $settersMap['setType'],
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
    public function createAccount(EntityDir\Report\Report $report, array $settersMap = [])
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
    public function createContact(EntityDir\Report\Report $report, array $settersMap = [])
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
    public function createVisitsCare(EntityDir\Report\Report $report, array $settersMap = [])
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
    public function createAsset($type, EntityDir\Report\Report $report, array $settersMap = [])
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
    public function createReportExpense($type, EntityDir\Report\Report $report, array $settersMap = [])
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
    public function createDecision(EntityDir\Report\Report $report, array $settersMap = [])
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
    public function createNote(EntityDir\Client $client, EntityDir\User $createdBy, $cat, $title, $content)
    {
        $note = new EntityDir\Note($client, $cat, $title, $content);
        $note->setCreatedBy($createdBy);

        $this->em->persist($note);

        return $note;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
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
     * @throws \Doctrine\ORM\ORMException
     */
    public function createOrganisation(string $name, string $identifier, bool $isActive): Organisation
    {
        $org = new EntityDir\Organisation();
        $org->setName($name);
        $org->setEmailIdentifier($identifier);
        $org->setIsActivated($isActive);

        $this->em->persist($org);

        return $org;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
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
     * @throws \Doctrine\ORM\ORMException
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
     * @throws \Doctrine\ORM\ORMException
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
    public function getReportFreshSectionStatus(EntityDir\Report\Report $report, string $section)
    {
        return $this->getReportById($report->getId())->getStatus()->getSectionStateNotCached($section);
    }

    /**
     * @return EntityDir\User
     */
    public function findUserByEmail(string $email)
    {
        return $this->getRepo('User')->findOneBy(['email' => $email]);
    }

    /**
     * @param string $deputyNo
     *
     * @return EntityDir\NamedDeputy
     */
    public function findNamedDeputyByNumber($deputyNo)
    {
        return $this->getRepo('NamedDeputy')->findOneBy(['deputyNo' => $deputyNo]);
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
        $tables = array_merge(['document', 'casrec', 'deputy_case', 'report_submission', 'report', 'odr', 'satisfaction'], $additionalTables);
        self::pgCommand('PGOPTIONS=\'--client-min-messages=warning\' psql -c "truncate table '.implode(',', $tables).'  RESTART IDENTITY cascade";');
    }

    public function refresh($entity)
    {
        $this->em->refresh($entity);
    }
}
