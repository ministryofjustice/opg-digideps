<?php

namespace AppBundle\Service\DataMigration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MigrationTest extends WebTestCase
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        // create client
        $client = self::createClient(['environment' => 'test',
                    'debug' => true, ]);
        $this->em = $client->getContainer()->get('em');
    }

    public function testMigrateToLastOne()
    {
//        $this->importDb('v055.test.sql');
//        $this->migrate('065');
//
//        $rows = $this->em->getConnection()->query('SELECT * from transaction WHERE amount IS NULL LIMIT 1')->fetchAll();
//        $this->assertEquals(null, $rows[0]['amounts']);
//
//        $rows = $this->em->getConnection()->query('SELECT * from transaction WHERE amount = 0.0 LIMIT 1')->fetchAll();
//        $this->assertEquals('0.0', $rows[0]['amounts']);
//
//        $rows = $this->em->getConnection()->query('SELECT * from transaction WHERE amount > 0 LIMIT 1')->fetchAll();
//        $this->assertEquals($rows[0]['amount'], $rows[0]['amount']);
    }

    public function testMigrateDown()
    {
//        $this->importDb('v055.test.sql');
//
//        $this->migrate('065');
//        $this->migrate('061');
//        $this->migrate('065');
//        $this->migrate('061');
//        $this->migrate('065');
    }

    private function migrate($to)
    {
//        ob_start();
//        exec("php app/console doctrine:migrations:migrate --no-interaction --env=test $to  -vvv", $out, $returnVar);
//        if ($returnVar != 0) {
//            echo ob_get_clean();
//            $this->fail('Migration did not return 0, see output above');
//        }
//        ob_clean();
    }

    private function importDb($file)
    {
        // import database with a subset of production data
//        $export = 'export PGHOST=postgres; export PGPASSWORD=api; export PGDATABASE=digideps_unit_test; export PGUSER=api;';
//        exec("$export psql -U api < ".__DIR__.'/'.$file, $out2);
    }
}
