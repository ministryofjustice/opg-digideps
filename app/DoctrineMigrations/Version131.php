<?php

namespace Application\Migrations;

use AppBundle\Service\FixDataService;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Version131 extends AbstractMigration  implements ContainerAwareInterface
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
        $em = $this->container->get('em');

        $fixDataService = new FixDataService($em);
        echo "Fixing missing data in reports (might take several minutes)...\n";
        $messages = $fixDataService->fixReports()->getMessages();
        echo "FixData results: " . print_r($messages, true);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
