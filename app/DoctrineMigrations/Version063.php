<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version063 extends AbstractMigration   implements ContainerAwareInterface
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
         ini_set('memory_limit', '4G');
         
         // convert transaction.amount into transaction.amounts
        $count = 0;
        $em = $this->container->get('em'); /* @var $em \Doctrine\ORM\EntityManager */
        $em->beginTransaction();
        foreach($em->getRepository('AppBundle\Entity\Transaction')->findAll() as $t) {
            if ($t->getAmount()) {
                $t->setAmounts([$t->getAmount()]);
                $em->flush($t);
                $count++;
            }
        }
        $em->commit();
        
        echo "Converted $count transactions to new array format\n";
        
        $this->addSql('SELECT MAX(version) from migrations');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('UPDATE transaction SET amounts = NULL');
    }
}
