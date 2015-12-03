<?php

namespace AppBundle\Service\DataMigration;

use PDO;

class AccountMigrationTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $export = "export PGHOST=postgres; export PGPASSWORD=api; export PGDATABASE=digideps_unit_test; export PGUSER=api;";

        exec("$export psql -U api -c 'DROP SCHEMA IF EXISTS public cascade'", $out1);
        exec("$export psql -U api < ".__DIR__."/oldTransactions.sql" , $out2);
        $this->assertCount(211, $out2, "cannot import SQL file for account migration testing");



    }

    public function testMigrateAccounts()
    {

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