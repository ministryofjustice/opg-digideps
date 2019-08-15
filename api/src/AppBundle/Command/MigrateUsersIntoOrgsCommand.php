<?php

namespace AppBundle\Command;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class MigrateUsersIntoOrgsCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this->setName('digideps:users-into-orgs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('em');

        /** @var OrganisationFactory $orgFactory */
        $orgFactory = $this->getContainer()->get('AppBundle\Factory\OrganisationFactory');

        /** @var OrganisationRepository $orgRepo */
        $orgRepo = $em->getRepository(Organisation::class);

        $profNamedUsers = $em->getRepository(User::class)->findBy(['roleName' => [
            User::ROLE_PROF_NAMED,
            User::ROLE_PA_NAMED
        ]]);

        foreach ($profNamedUsers as $user) {

            // Create a new Organisation if first occurrence of email domain.
            if (null === ($organisation = $orgRepo->findByEmailIdentifier($user->getEmail()))) {
                $organisation = $orgFactory->createFromFullEmail($user->getEmail(), $user->getEmail());
                $em->persist($organisation);
            }

            $this->attachUserToOrganisation($organisation, $user);
            $this->attachUsersTeamMembersToOrganisation($organisation, $user->getTeams());

            $em->flush();
        }
    }

    /**
     * @param Organisation $organisation
     * @param User $user
     */
    private function attachUserToOrganisation(Organisation $organisation, User $user): void
    {
        $organisation->addUser($user);
        $user->setOrganisation($organisation);
    }

    /**
     * @param Organisation $organisation
     * @param Collection $teams
     */
    protected function attachUsersTeamMembersToOrganisation(Organisation $organisation, Collection $teams): void
    {
        foreach ($teams as $team) {
            foreach ($team->getMembers() as $user) {
                $this->attachUserToOrganisation($organisation, $user);
            }
        }
    }
}
