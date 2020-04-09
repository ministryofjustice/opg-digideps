<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\User;
use AppBundle\Service\OrgService;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/team")
 */
class TeamController extends RestController
{
    /**
     * @Route("/members", methods={"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getMembers(Request $request)
    {
        $groups = $request->query->has('groups') ?
            (array) $request->query->get('groups') :
            ['team', 'team-users', 'user', 'team-names'];

        $this->setJmsSerialiserGroups($groups);

        /** @var User $user */
        $user = $this->getUser();

        return $user->getMembersInAllTeams();
    }

    /**
     * @Route("/member/{id}", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getMemberById($id, OrgService $orgService)
    {
        $this->setJmsSerialiserGroups(['team', 'team-users', 'user', 'team-names']);

        /** @var User $user */
        $user = $this->getUser();

        return $orgService->getMemberById($user, $id);
    }

    /**
     * Add the user (retrieved by Id) to the teams the current user belongs to
     *
     * @Route("/add-to-team/{userId}", methods={"PUT"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function addToTeam($userId, OrgService $orgService)
    {
        /** @var User $user */
        $user = $this->findEntityBy(User::class, $userId, 'User not found');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $orgService->addUserToUsersClients($currentUser, $user);
        $orgService->addUserToUsersTeams($currentUser, $user);

        return ['id' => $user->getId()];
    }

    /**
     * Delete Org team membership, and also the user if belonging to no teams
     *
     * @Route("/delete-membership/{userId}", methods={"DELETE"})
     * @Security("has_role('ROLE_ORG_NAMED') or has_role('ROLE_ORG_ADMIN')")
     *
     * @param Request $request
     * @param string     $userId
     *
     * @return array
     */
    public function deleteOrgTeamUser(string $userId, OrgService $orgService)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var User $user */
        $user = $orgService->getMemberById($currentUser, $userId);
        $orgService->removeUserFromTeamsOf($currentUser, $user);

        return [];
    }
}
