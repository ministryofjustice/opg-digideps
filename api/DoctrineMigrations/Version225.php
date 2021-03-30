<?php

declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Organisation;
use App\Entity\Repository\OrganisationRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
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

        /** @var EntityManager $em */
        $em = $this->container->get('em');

        /** @var OrganisationRepository $orgRepo */
        $orgRepo = $em->getRepository(Organisation::class);

        foreach ($orgRepo->getOrgIdAndNames() as $key => $value) {
            if (strpos($value, "@") !== false) {
                $org = $orgRepo->find($key);
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
