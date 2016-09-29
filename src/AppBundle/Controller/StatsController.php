<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/stats")
 */
class StatsController extends RestController
{
    /**
     * @Route("/users")
     * @Method({"GET"})
     */
    public function users(Request $request)
    {
        $ret = [];
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        //$deputy = $this->getRepository('Role')->findBy(['role'=>'ROLE_LAY_DEPUTY']);
        // pre-join data to reduce number of queries
        // $users = $this->getRepository('User')->findBy(['role'=>$deputy], ['id' => 'DESC']);
        $qb = $this->getEntityManager()->createQuery(
            "SELECT u, c, r, role FROM AppBundle\Entity\User u
                LEFT JOIN u.role role
                LEFT JOIN u.clients c
                LEFT JOIN c.reports r
                WHERE role.role = 'ROLE_LAY_DEPUTY' ORDER BY u.id DESC");

        if ($maxResults = $request->query->get('limit')) {
            $qb->setMaxResults($maxResults);
        }
        $users = $qb->getResult();

        // alternative without join and lazy-loading
        // $deputy = $this->getRepository('Role')->findBy(['role'=>'ROLE_LAY_DEPUTY']);
        // $users = $this->getRepository('User')->findBy(['role'=>$deputy], ['id' => 'DESC']);

        foreach ($users as $user) {
            /* @var $user EntityDir\User */
            $row = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'registration_date' => $user->getRegistrationDate() ? $user->getRegistrationDate()->format('Y-m-d') : '-',
                'last_logged_in' => $user->getLastLoggedIn() ? $user->getLastLoggedIn()->format('Y-m-d H:i:s') : '-',
                'client_name' => 'n.a.',
                'client_lastname' => 'n.a.',
                'client_casenumber' => 'n.a.',
                'client_court_order_date' => 'n.a.',
                'total_reports' => 0,
                'active_reports' => 0,
            ];

            foreach ($user->getClients() as $client) {
                $row['client_name'] = $client->getFirstname();
                $row['client_lastname'] = $client->getLastname();
                $row['client_casenumber'] = $client->getCaseNumber();
                $row['client_court_order_date'] = $client->getCourtDate() ? $client->getCourtDate()->format('Y-m-d') : '-';
                foreach ($client->getReports() as $report) {
                    ++$row['total_reports'];
                    if ($report->getSubmitted()) {
                        continue;
                    }
                    ++$row['active_reports'];
                }
            }

            $ret[] = $row;
        }

        $this->get('kernel.listener.responseConverter')->addContextModifier(function ($context) {
            $context->setSerializeNull(true);
        });

        return $ret;
    }

    /**
     * @param $sql
     *
     * @return array
     */
    private function getQueryResults($sql)
    {
        $connection = $this->get('em')->getConnection();

        return $connection->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
