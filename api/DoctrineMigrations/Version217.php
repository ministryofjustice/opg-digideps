<?php declare(strict_types=1);

namespace Application\Migrations;

use App\Entity\Organisation;
use App\Entity\Repository\OrganisationRepository;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version217 extends AbstractMigration implements ContainerAwareInterface
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

        /** @var OrganisationFactory $orgFactory */
        $orgFactory = $this->container->get('App\Factory\OrganisationFactory');

        /** @var OrganisationRepository $orgRepo */
        $orgRepo = $em->getRepository(Organisation::class);

        foreach ($namedUsers as $user) {

            // Create a new Organisation if first occurrence of email domain.
            if (null === ($organisation = $orgRepo->findByEmailIdentifier($user->getEmail()))) {
                try {
                    $organisation = $orgFactory->createFromFullEmail($user->getEmail(), $user->getEmail());
                    $em->persist($organisation);
                } catch (\InvalidArgumentException $e) {
                    continue;
                }
            }

            $organisation->addUser($user);
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
                $organisation->addUser($user);
            }
        }
    }

    public function down(Schema $schema) : void
    {
    }
}
