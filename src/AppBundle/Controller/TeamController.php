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
     * Delete Org team member user.
     *
     * @Route("/delete-user/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_ORG_NAMED') or has_role('ROLE_ORG_ADMIN')")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return array
     */
    public function deleteOrgTeamUser(Request $request, $id)
    {
        if (!array_key_exists($id, $this->getUser()->getMembersInAllTeams())) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        /* @var $user EntityDir\User */
        $user = $this->getMemberById($request, $id);

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return [];
    }
}
