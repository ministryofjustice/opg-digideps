<?php

namespace AppBundle\Service\DataMigration;

use PDO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountMigrationTest extends WebTestCase
{
    /**
     * @var AccountMigration
     */
    private $am;

    public function setUp()
    {
        // import database at version 4 with some old account and transactions
        $export = "export PGHOST=postgres; export PGPASSWORD=api; export PGDATABASE=digideps_unit_test; export PGUSER=api;";
        exec("$export psql -U api -c 'DROP SCHEMA IF EXISTS public cascade'", $out1);
        exec("$export psql -U api < ".__DIR__."/oldTransactions.sql" , $out2);

        //migrate from version 47 (that will test the migration too)
        exec('php app/console doctrine:migrations:migrate --no-interaction --env=test');

        $client = self::createClient([ 'environment' => 'test',
            'debug' => true ]);
        $em = $client->getContainer()->get('em');

        $this->am = new AccountMigration($em->getConnection());
        $this->initialReports = $this->am->getReports();

        $this->assertCount(2, $this->initialReports, '#reports mismatch');

        //report 1
        $this->assertCount(0, $this->initialReports[1]['transactions_new']);
        $this->assertEquals(0, $this->initialReports[1]['transactions_new_sum']['in'], '', 0.1);
        $this->assertEquals(0, $this->initialReports[1]['transactions_new_sum']['out'], '', 0.1);
        $this->assertCount(1, $this->initialReports[1]['accounts']);
        // 1st account
        $this->assertCount(40, $this->initialReports[1]['accounts'][1]['transactions_old']);
        $this->assertEquals(2.00, $this->initialReports[1]['accounts'][1]['transactions_old']['attendance_allowance']['amount']);
        $this->assertEquals(190.0, $this->initialReports[1]['accounts'][1]['transactions_old_sum']['in']);
        $this->assertEquals(630.0, $this->initialReports[1]['accounts'][1]['transactions_old_sum']['out']);

        //report 2
        $this->assertCount(0, $this->initialReports[2]['transactions_new']);
        $this->assertEquals(0, $this->initialReports[2]['transactions_new_sum']['in']);
        $this->assertEquals(0, $this->initialReports[2]['transactions_new_sum']['out']);
        $this->assertCount(2, $this->initialReports[2]['accounts']);
        // 1st account
        $this->assertCount(40,  $this->initialReports[2]['accounts'][2]['transactions_old']);
        $this->assertEquals(101.1,  $this->initialReports[2]['accounts'][2]['transactions_old_sum']['in'], '', 0.1);
        $this->assertEquals(102,  $this->initialReports[2]['accounts'][2]['transactions_old_sum']['out'], '', 0.1);
        // 2nd account
        $this->assertCount(40,  $this->initialReports[2]['accounts'][3]['transactions_old']);
        $this->assertEquals(91,  $this->initialReports[2]['accounts'][3]['transactions_old_sum']['in'], '', 0.1);
        $this->assertEquals(92,  $this->initialReports[2]['accounts'][3]['transactions_old_sum']['out']);

//        file_put_contents(__DIR__ . '/input.txt', print_r($this->initialReports, true));
    }

    public function testMigrateAccounts()
    {
        $this->am->migrateAccounts();

        // get updated data
        $reports = $this->am->getReports();

//        file_put_contents(__DIR__ . '/output.txt', print_r($reports, true));

        //report 1
        $report = $reports[1];
        $this->assertCount(40, $report['transactions_new']);
        $this->assertEquals($this->initialReports[1]['accounts'][1]['transactions_old_sum']['in'], $report['transactions_new_sum']['in'], '', 0.1);
        $this->assertEquals($this->initialReports[1]['accounts'][1]['transactions_old_sum']['out'], $report['transactions_new_sum']['out'], '', 0.1);

        //report 2
        $report = $reports[2];
        $this->assertCount(40, $report['transactions_new']);
        $this->assertEquals($this->initialReports[2]['accounts'][2]['transactions_old_sum']['in'] + $this->initialReports[2]['accounts'][3]['transactions_old_sum']['in'], $report['transactions_new_sum']['in'], '', 0.1);
        $this->assertEquals($this->initialReports[2]['accounts'][2]['transactions_old_sum']['out'] +  $this->initialReports[2]['accounts'][3]['transactions_old_sum']['out'], $report['transactions_new_sum']['out']);
    }

//    public function tearDown()
//    {
//        exec('php app/console cache:clear --env=test');
//        exec('php app/console doctrine:query:sql "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" --env=test');
//        exec('php app/console doctrine:migrations:migrate --no-interaction --env=test');
//        exec('php app/console doctrine:schema:validate --env=test');
//        exec('php app/console digideps:add-user deputy@example.org --firstname=test --lastname=deputy --role=2 --password=Abcd1234 --env=test');
//        exec('php app/console digideps:add-user admin@example.org --firstname=test --lastname=admin  --role=1 --password=Abcd1234 --env=test');
//    }

}