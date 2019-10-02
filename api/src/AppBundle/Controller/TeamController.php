<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/team")
 */
class TeamController extends RestController
{
    /**
     * @Route("/members")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getMembers(Request $request)
    {
        $groups = $request->query->has('groups') ?
            (array) $request->query->get('groups') :
            ['team', 'team-users', 'user', 'team-names'];

        $this->setJmsSerialiserGroups($groups);

        return $this->getUser()->getMembersInAllTeams();
    }

    /**
     * @Route("/member/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getMemberById(Request $request, $id)
    {
        $this->setJmsSerialiserGroups(['team', 'team-users', 'user', 'team-names']);

        return $this->orgService()
            ->getMemberById($this->getUser(), $id);
    }

    /**
     * Add the user (retrieved by Id) to the teams the current user belongs to
     *
     * @Route("/add-to-team/{userId}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function addToTeam(Request $request, $userId)
    {
        $user = $this->findEntityBy(EntityDir\User::class, $userId, 'User not found');
        /* @var $user EntityDir\User */

        $this->orgService()->addUserToUsersClients($this->getUser(), $user);
        $this->orgService()->addUserToUsersTeams($this->getUser(), $user);

        return ['id' => $user->getId()];
    }

    /**
     * Delete Org team membership, and also the user if belonging to no teams
     *
     * @Route("/delete-membership/{userId}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_ORG_NAMED') or has_role('ROLE_ORG_ADMIN')")
     *
     * @param Request $request
     * @param int     $userId
     *
     * @return array
     */
    public function deleteOrgTeamUser(Request $request, $userId)
    {
        /* @var $user EntityDir\User */
        $user = $this->orgService()->getMemberById($this->getUser(), $userId);
        $this->orgService()->removeUserFromTeamsOf($this->getUser(), $user);

        return [];
    }

    /**
     * @return \AppBundle\Service\OrgService
     */
    private function orgService()
    {
        return $this->get('org_service');
    }
}
