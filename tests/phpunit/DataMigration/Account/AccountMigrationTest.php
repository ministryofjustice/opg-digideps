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
//        exec("$export psql -U api -c 'DROP SCHEMA IF EXISTS public cascade'", $out1);
        exec("$export psql -U api < ".__DIR__."/oldTransactions.v047.sql" , $out2);

        //migrate from version 47 (that will test the migration too)
        exec('php app/console doctrine:migrations:migrate --no-interaction --env=test 051');

        // create client
        $client = self::createClient([ 'environment' => 'test',
            'debug' => true ]);
        $em = $client->getContainer()->get('em');

        // create class
        $this->am = new AccountMigration($em->getConnection());

        /// assert added data from SQL is correct
        $this->initialReports = $this->am->getReports();
        $this->account1 = $this->initialReports[1]['accounts'][1];
        $this->account2 = $this->initialReports[2]['accounts'][2];
        $this->account3 = $this->initialReports[2]['accounts'][3];


        $this->assertCount(2, $this->initialReports, '#reports mismatch');

        //report 1
        $this->assertCount(0, $this->initialReports[1]['transactions_new']);
        $this->assertEquals(0, $this->initialReports[1]['transactions_new_sum']['in'], '', 0.1);
        $this->assertEquals(0, $this->initialReports[1]['transactions_new_sum']['out'], '', 0.1);
        $this->assertCount(1, $this->initialReports[1]['accounts']);
        // 1st account
        $this->assertCount(40, $this->account1['transactions_old']);
        $this->assertEquals(2.00, $this->account1['transactions_old']['attendance_allowance']['amount']);
        $this->assertEquals(190.0, $this->account1['transactions_old_sum']['in']);
        $this->assertEquals(630.0, $this->account1['transactions_old_sum']['out']);

        //report 2
        $this->assertCount(0, $this->initialReports[2]['transactions_new']);
        $this->assertEquals(0, $this->initialReports[2]['transactions_new_sum']['in']);
        $this->assertEquals(0, $this->initialReports[2]['transactions_new_sum']['out']);
        $this->assertCount(2, $this->initialReports[2]['accounts']);
        // 1st account
        $this->assertCount(40,  $this->account2['transactions_old']);
        $this->assertEquals(102.2,  $this->account2['transactions_old_sum']['in'], '', 0.1);
        $this->assertEquals(102,  $this->account2['transactions_old_sum']['out'], '', 0.1);
        // 2nd account
        $this->assertCount(40,  $this->account3['transactions_old']);
        $this->assertEquals(94.4,  $this->account3['transactions_old_sum']['in'], '', 0.1);
        $this->assertEquals(92,  $this->account3['transactions_old_sum']['out']);
        // single transaction check
        $this->assertEquals(1.20, $this->account2['transactions_old']['compensation_or_damages_awards']['amount']);
        $this->assertEquals('cda_desc1', $this->account2['transactions_old']['compensation_or_damages_awards']['more_details']);
        $this->assertEquals(3.40, $this->account3['transactions_old']['compensation_or_damages_awards']['amount']);
        $this->assertEquals('cda_desc2', $this->account3['transactions_old']['compensation_or_damages_awards']['more_details']);

    }

    public function testMigrateAccounts()
    {
        // migrate until 52
        exec('php app/console doctrine:migrations:migrate --no-interaction --env=test', $out);
        $this->assertContains('added 80 new transactions', implode("",$out));

        //$this->am->migrateAccounts();

        // get updated data
        $reports = $this->am->getReports();

        //report 1
        $report = $reports[1];
        $this->assertCount(40, $report['transactions_new']);
        $this->assertEquals($this->account1['transactions_old_sum']['in'], $report['transactions_new_sum']['in'], '', 0.1);
        $this->assertEquals($this->account1['transactions_old_sum']['out'], $report['transactions_new_sum']['out'], '', 0.1);

        //report 2
        $report = $reports[2];
        $this->assertCount(40, $report['transactions_new']);
        // check totals in and out are the sum of the two report's accounts
        $this->assertEquals($this->account2['transactions_old_sum']['in'] + $this->account3['transactions_old_sum']['in'], $report['transactions_new_sum']['in'], '', 0.1);
        $this->assertEquals($this->account2['transactions_old_sum']['out'] +  $this->account3['transactions_old_sum']['out'], $report['transactions_new_sum']['out']);
        // check single transaction: amount is the sum, and more_details is merged
        $this->assertEquals($this->account2['transactions_old']['compensation_or_damages_awards']['amount'] + $this->account3['transactions_old']['compensation_or_damages_awards']['amount'], $report['transactions_new']['compensation-or-damages-award']['amount']);
        $this->assertEquals($this->account2['transactions_old']['compensation_or_damages_awards']['more_details']."\n".$this->account3['transactions_old']['compensation_or_damages_awards']['more_details'], $report['transactions_new']['compensation-or-damages-award']['more_details']);

        file_put_contents(__DIR__ . '/new.array', print_r($reports, true));
        file_put_contents(__DIR__ . '/old.array', print_r($this->initialReports, true));
    }

    public function tearDown()
    {
        // execute this test separately so that the "src" tests don't get affected
    }

}