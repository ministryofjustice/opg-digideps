<?php

namespace Application\Migrations;

use AppBundle\Service\DataMigration\AccountMigration;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * add transactions to reports already existing before branch accounts_mk2 is merged
 * KEEP THIS ONE LAST.
 */
class Version053 extends AbstractMigration implements ContainerAwareInterface
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

        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $em = $this->container->get('em');
        $am = new AccountMigration($em->getConnection());
        $ret = $am->addMissingTransactions();

        ini_set('memory_limit', $memLimitInit);

        $this->addSql('SELECT MAX(version) from migrations');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}
