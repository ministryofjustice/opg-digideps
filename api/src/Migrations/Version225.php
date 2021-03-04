<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Organisation;
use App\Repository\OrganisationRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version225 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription() : string
    {
        return 'Change all Org names that do not contain an @ to "Your Organisation"';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }

    public function postUp(Schema $schema) : void
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var OrganisationRepository $orgRepo */
        $orgRepo = $em->getRepository(Organisation::class);

        foreach ($orgRepo->findAll() as $org) {
            if (strpos($org->getName(), "@") !== false) {
                $org->setName('Your Organisation');
                $em->persist($org);
            }
        }

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
    }
}
