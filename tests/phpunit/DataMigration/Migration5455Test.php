<?php

namespace AppBundle\Service\DataMigration;

use PDO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class Migration5455Test extends WebTestCase
{
    /**
     * @var SafeGuardMigration
     */
    private $object;

    public function setUp()
    {
        // import database at version 4 with some old account and transactions
        $export = "export PGHOST=postgres; export PGPASSWORD=api; export PGDATABASE=digideps_unit_test; export PGUSER=api;";
//        exec("$export psql -U api -c 'DROP SCHEMA IF EXISTS public cascade'", $out1);
        exec("$export psql -U api < ".__DIR__."/v053.test.sql" , $out2);
        
        exec('php app/console doctrine:migrations:migrate --no-interaction --env=test -vvv 054', $out);
        echo implode("\n", $out);
        
        // create client
        $client = self::createClient([ 'environment' => 'test',
            'debug' => true ]);
        $em = $client->getContainer()->get('em');

        // create class
        $this->object = new SafeGuardMigration($em->getConnection());

        /// assert added data from SQL is correct
        $this->initialReports = $this->object->getReports();
        
        // r1
        $this->assertEquals(false, $this->initialReports[1]['safeg']);
        // r2
        $this->assertEquals('yes', $this->initialReports[2]['safeg']['do_you_live_with_client']);
        // r3
        $this->assertEquals('no', $this->initialReports[3]['safeg']['do_you_live_with_client']);
        $this->assertEquals('everyday', $this->initialReports[3]['safeg']['how_often_do_you_visit']);
        $this->assertEquals('once_a_week', $this->initialReports[3]['safeg']['how_often_do_you_phone_or_video_call']);
        $this->assertEquals('once_a_month', $this->initialReports[3]['safeg']['how_often_do_you_write_email_or_letter']);
        $this->assertEquals('more_than_twice_a_year', $this->initialReports[3]['safeg']['how_often_does_client_see_other_people']);
        $this->assertEquals('', $this->initialReports[3]['safeg']['anything_else_to_tell']);
        // r4
        $this->assertEquals('no', $this->initialReports[4]['safeg']['do_you_live_with_client']);
        $this->assertEquals('less_than_once_a_year', $this->initialReports[4]['safeg']['how_often_do_you_visit']);
        $this->assertEquals('once_a_year', $this->initialReports[4]['safeg']['how_often_do_you_phone_or_video_call']);
        $this->assertEquals('more_than_twice_a_year', $this->initialReports[4]['safeg']['how_often_do_you_write_email_or_letter']);
        $this->assertEquals('once_a_month', $this->initialReports[4]['safeg']['how_often_does_client_see_other_people']);
        $this->assertContains('first line', $this->initialReports[4]['safeg']['anything_else_to_tell']);
        $this->assertContains('excl mark !', $this->initialReports[4]['safeg']['anything_else_to_tell']);
        $this->assertContains('line before', $this->initialReports[4]['safeg']['anything_else_to_tell']);

    }

    public function testMigrate053To54()
    {
//        $this->object->migrateAll(); //only use to debug errors
        
        exec('php app/console doctrine:migrations:migrate --no-interaction --env=test -vvv 055', $out);
        echo implode("\n", $out);
        
        // get updated data
        $reports = $this->object->getReports();
        // r1
        $this->assertEquals(false, $reports[1]['safeg']);
        // r2
        $this->assertEquals('yes', $reports[2]['safeg']['do_you_live_with_client']);
        // r3
        $this->assertEquals('no', $reports[3]['safeg']['do_you_live_with_client']);
        $actual = $reports[3]['safeg']['how_often_contact_client'];
        $this->assertNotNull($actual, "migration not executed");
        $this->assertContains("I (or other deputies) visit TestName Every day\r\n", $actual);
        $this->assertContains("phone or video call TestName At least once a week\r\n", $actual);
        $this->assertContains("write emails or letters to TestName At least once a month\r\n", $actual);
        $this->assertContains("sees other people More than twice a year\r\n", $actual);
        // r4
        $this->assertEquals("no", $reports[4]["safeg"]["do_you_live_with_client"]);
        $actual = $reports[4]["safeg"]["how_often_contact_client"];
//        var_dump($actual);
        $this->assertContains("visit TestName Less than once a year\r\n", $actual);
        $this->assertContains("phone or video call TestName Once a year\r\n", $actual);
        $this->assertContains("rite emails or letters to TestName More than twice a year\r\n", $actual);
        $this->assertContains("sees other people At least once a month\r\n", $actual);
        $this->assertContains("Anything else: first line\r\nsecond line", $actual); //test new lines
        $this->assertContains("tags <b>bold</b>\r\n", $actual);
        
    }

}