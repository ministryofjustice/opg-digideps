<?php

namespace Application\Migrations;

use AppBundle\Entity\Report\Report;
use AppBundle\Service\FixDataService;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * clean up merged migrations
 */
class Version130 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach(range(73, 115) as $m) {
            $this->addSql("DELETE FROM migrations where version = '" . sprintf('%03d', $m)."'");
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
