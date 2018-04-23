<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        $this->setJmsSerialiserGroups(['team', 'team-users', 'user', 'team-names']);

        return $this->getUser()->getMembersInAllTeams();
    }

    /**
     * @Route("/member/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getMemberById(Request $request, $id)
    {
        $user = $this->getRepository(EntityDir\User::class)->find($id);
        if (!array_key_exists($id, $this->getUser()->getMembersInAllTeams())) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        $this->setJmsSerialiserGroups(['team', 'team-users', 'user', 'team-names']);

        return $user;
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

        foreach($this->getUser()->getTeams() as $team) {
            $user->addTeam($team);
        }

        $this->getEntityManager()->flush();

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
        if (!array_key_exists($userId, $this->getUser()->getMembersInAllTeams())) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        /* @var $user EntityDir\User */
        $user = $this->getMemberById($request, $userId);


        // remove user from teams the logged-user (operation performer) belongs to
        foreach($this->getUser()->getTeams() as $team) {
            $user->getTeams()->removeElement($team);
        }

        // remove user if belonging to no teams
        if (count($user->getTeams()) === 0) {
            $this->getEntityManager()->remove($user);
        }

        $this->getEntityManager()->flush();

        return [];
    }
}
