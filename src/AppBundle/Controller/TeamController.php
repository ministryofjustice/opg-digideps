<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/team")
 */
class TeamController extends RestController
{
    /**
     * @Route("/members")
     * @Method({"GET"})
     */
    public function getMembers(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_PA]);

        $this->setJmsSerialiserGroups(['team', 'team-users', 'user']);

        $team = $this->getUser()->getTeams()->first(); /* @var $team EntityDir\Team */
        if (!$team) {
            return [];
        }

        return $team->getMembers();
    }

    /**
     * @Route("/member/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getMemberById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_PA]);

        $user = $this->getRepository(EntityDir\User::class)->find($id);
        if ($user->getTeams()->first() !== $this->getUser()->getTeams()->first()) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        $this->setJmsSerialiserGroups(['team', 'team-users', 'user']);


        return $user;
    }

    /**
     * Delete PA team member user.
     *
     * @Route("/delete-user/{id}")
     * @Method({"DELETE"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array
     */
    public function deletePaTeamUser(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(
            [
                EntityDir\User::ROLE_PA_NAMED,
                EntityDir\User::ROLE_PA_ADMIN
            ]
        );

        /* @var $user EntityDir\User */
        $user = $this->getMemberById($request, $id);

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return [];
    }
}
