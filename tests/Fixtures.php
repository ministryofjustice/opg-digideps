<?php

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManager;

/**
 * Used for unit testing.
 */
class Fixtures
{
    const   PG_DUMP_PATH = '/tmp/dd_phpunit.pgdump';

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        $user->setEmail('temp' . microtime(1) . rand(100, 99999) . '@temp.com');
        $user->setPassword('temp@temp.com');
        $user->setFirstname('name' . time());
        $user->setLastname('surname' . time());

        foreach ($settersMap as $k => $v) {
            $user->$k($v);
        }

        $this->em->persist($user);

        return $user;
    }

    /**
     * @return EntityDir\CasRec
     */
    public function createCasRec(
        EntityDir\Client $client,
        EntityDir\User $user,
        AppBundle\Entity\Report\Report $report,
        $corref = 'null',
        array $settersMap = []
    ) {
        // add casRec
        $casRec = new EntityDir\CasRec(
            $client->getCaseNumber(),
            $client->getLastname(),
            $user->getDeputyNo(),
            $user->getLastname(),
            $user->getAddressPostcode(),
            $report->getType(),
            $corref
        );

        foreach ($settersMap as $k => $v) {
            $casRec->$k($v);
        }

        $this->em->persist($casRec);

        return $casRec;
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
     * @param EntityDir\Client $client
     * @param array            $settersMap
     *
     * @return EntityDir\Odr\Odr
     */
    public function createOdr(EntityDir\Client $client, array $settersMap = [])
    {
        $odr = new EntityDir\Odr\Odr($client);

        foreach ($settersMap as $k => $v) {
            $odr->$k($v);
        }

        $this->em->persist($odr);

        return $odr;
    }

    /**
     * @return EntityDir\Report\Report
     */
    public function createReport(EntityDir\Client $client, array $settersMap = [])
    {
        $report = new EntityDir\Report\Report($client);

        // start/end dates from today for 365 days
        $today = new DateTime();
        $report->setStartDate($today);
        $today->modify('+365 days');
        $report->setEndDate($today);

        $report->setType(EntityDir\Report\Report::TYPE_102);
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
     * @return EntityDir\Odr\BankAccount
     */
    public function createOdrAccount(EntityDir\Odr\Odr $odr, array $settersMap = [])
    {
        $ret = new EntityDir\Odr\BankAccount();
        $ret->setOdr($odr);
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
        $contact->setAddress('address' . time());

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
     * @param EntityDir\Odr\Odr $odr
     * @param array             $settersMap
     *
     * @return EntityDir\Odr\VisitsCare
     */
    public function createOdrVisitsCare(EntityDir\Odr\Odr $odr, array $settersMap = [])
    {
        $vc = new EntityDir\Odr\VisitsCare();
        $vc->setOdr($odr);
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
     * @return EntityDir\Odr\Asset
     */
    public function createOdrAsset($type, EntityDir\Odr\Odr $odr, array $settersMap = [])
    {
        $asset = EntityDir\Odr\Asset::factory($type);
        $asset->setOdr($odr);

        foreach ($settersMap as $k => $v) {
            $asset->$k($v);
        }
        $this->em->persist($asset);

        return $asset;
    }

    /**
     * @return EntityDir\Odr\Expense
     */
    public function createOdrExpense($type, EntityDir\Odr\Odr $odr, array $settersMap = [])
    {
        $record = new EntityDir\Odr\Expense($odr);
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
        $decision->setDescription('description' . time());

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

    public function persist()
    {
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
        return $this->em->getRepository("AppBundle\\Entity\\{$entity}");
    }

    /**
     * @param $id
     *
     * @return EntityDir\Report\Report
     */
    public function getReportById($id)
    {
        return $this->getRepo('Report\Report')->find($id);
    }

    /**
     * @param $email
     *
     * @return EntityDir\User
     */
    public function findUserByEmail($email)
    {
        return $this->getRepo('User')->findOneBy(['email'=>$email]);
    }

    public function getConnection()
    {
        return $this->em->getConnection();
    }

    private static function pgCommand($cmd)
    {
        exec(self::getPGExportCommand() . $cmd);
    }

    public static function initDb()
    {
    }

    public static function backupDb()
    {
        self::pgCommand('pg_dump --clean > ' . self::PG_DUMP_PATH);
    }

    public static function restoreDb()
    {
        if (!file_exists(self::PG_DUMP_PATH)) {
            throw new \RuntimeException(self::PG_DUMP_PATH . ' not found');
        }
        self::pgCommand('psql < ' . self::PG_DUMP_PATH);
    }

    public static function deleteReportsData($additionalTables = [])
    {
        $tables = array_merge(['casrec', 'deputy_case', 'report', 'odr', 'dd_team'], $additionalTables);
        self::pgCommand('PGOPTIONS=\'--client-min-messages=warning\' psql -c "truncate table ' . implode(',', $tables) . '  RESTART IDENTITY cascade";');
    }
}
