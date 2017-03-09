<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Doctrine\DBAL\Query\QueryBuilder;
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
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_PA, EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER]);

        $serialisedGroups = $request->query->has('groups')
            ? (array)$request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $team = $this->getUser()->getTeams()->first(); /* @var $team EntityDir\Team */
        if (!$team) {
            return [];
        }

        return $team->getMembers();
    }

}
