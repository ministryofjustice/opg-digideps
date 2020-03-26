<?php declare(strict_types=1);

namespace Application\Migrations;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version226 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema) : void
    {
        /** @var EntityManager $em */
        $em = $this->container->get('em');

        $namedUsers = $em->getRepository(User::class)->findBy(['roleName' => [
            User::ROLE_PROF_NAMED,
            User::ROLE_PA_NAMED
        ]]);

        /** @var OrganisationRepository $orgRepo */
        $orgRepo = $em->getRepository(Organisation::class);

        foreach ($namedUsers as $user) {
            $organisation = $orgRepo->findByEmailIdentifier($user->getEmail());

            if (null === $organisation) {
                continue;
            }

            if (count($user->getOrganisations()) === 0) {
                $organisation->addUser($user);
            }

            $this->attachUsersTeamMembersToOrganisation($organisation, $user->getTeams());

            $em->flush();
        }
    }

    /**
     * @param Organisation $organisation
     * @param Collection $teams
     */
    protected function attachUsersTeamMembersToOrganisation(Organisation $organisation, Collection $teams): void
    {
        foreach ($teams as $team) {
            foreach ($team->getMembers() as $user) {
                if (count($user->getOrganisations()) === 0) {
                    $organisation->addUser($user);
                }
            }
        }
    }

    public function down(Schema $schema) : void
    {

    }
}
