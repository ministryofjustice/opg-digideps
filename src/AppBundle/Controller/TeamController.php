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

        $this->setJmsSerialiserGroups(['team', 'team-users', 'user']);

        return $user;
    }


    /**
     * Retrieve user team info by email
     * Used when a new user is added from team page to do cross-team checks. For PROF only
     *
     * [
     *  belongsToOtherTeam => true/false
     *  userId => user ID (based on the email)
     *  teamId => Team ID
     * ]
     *
     * @throws RuntimeException if user already part of the team, or user belongs to two ore more teams
     *
     * @Route("/user-info-by-email/{email}")
     * @Method({"GET"})
     *
     * @Security("has_role('ROLE_PROF')")
     */
    public function teamInfo(Request $request, $email)
    {
        $loggedInUser = $this->getUser();
        $user = $this->getRepository(EntityDir\User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return;
        }
        $sameType = $user->isProfDeputy() && $loggedInUser->isProfDeputy();
        if (!$sameType) {
            return;
        }

        $loggedInUserTeams = $this->getUser()->getTeams();
        if (count($loggedInUserTeams) > 1) {
            throw new \RuntimeException('The logged user belongs to more than a team. Cannot chose one to add the existing user into');
        }
        $newTeamId = $loggedInUserTeams->first()->getId();
        if (isset($user->getTeamNames()[$newTeamId])) {
            throw new \RuntimeException('User already part of the team', 422); //TODO could be done in the view
        }

        return [
            'belongsToOtherTeam'=> count($user->getTeams()) > 0,
            'userId' => $user->getId(),
            'teamId'=>$newTeamId
        ];
    }


    /**
     * Delete Org team member user.
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
