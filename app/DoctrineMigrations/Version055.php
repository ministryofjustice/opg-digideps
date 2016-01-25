<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version055 extends AbstractMigration implements ContainerAwareInterface
{

    
    private $container;


    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $memLimitInit = ini_get('memory_limit');
        ini_set('memory_limit', '2048M');

        $em = $this->container->get('em');
        $am = new \AppBundle\Service\DataMigration\SafeGuardMigration($em->getConnection());
        $ret = $am->migrateAll();

        echo "Safe migration results = " . print_r($ret, true);

        ini_set('memory_limit', $memLimitInit);
    }


    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE safeguarding DROP how_often_contact_client');
    }

}