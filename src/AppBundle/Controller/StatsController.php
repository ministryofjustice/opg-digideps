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
        $qb = $this->get('em')->createQuery(
            "SELECT u, c, r, a, role FROM AppBundle\Entity\User u
                LEFT JOIN u.role role
                LEFT JOIN u.clients c
                LEFT JOIN c.reports r
                LEFT JOIN r.accounts a
                WHERE role.role = 'ROLE_LAY_DEPUTY'");
        $users = $qb->getResult();

        // alternative without join and lazy-loading
        // $deputy = $this->getRepository('Role')->findBy(['role'=>'ROLE_LAY_DEPUTY']);
        // $users = $this->getRepository('User')->findBy(['role'=>$deputy], ['id' => 'DESC']);

        foreach ($users as $user) { /* @var $user EntityDir\User */
            $row = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullname' => $user->getFirstname() . ' '.$user->getLastname(),
                'registration_date' => $user->getRegistrationDate() ? $user->getRegistrationDate()->format('Y-m-d') : '-',
                'last_logged_in' => $user->getLastLoggedIn() ?  $user->getLastLoggedIn()->format('Y-m-d') : '-',
                'is_active' => $user->getActive() ? 'true' : 'false',
                'client_fullname' => 'n.a.',
                'client_casenumber' => 'n.a.',
                'has_details' => $user->getAddress1() ? 'true' : 'false',
                'total_reports' => 0,
                'active_reports' => 0,
                'active_reports_due' => 0,
                'active_reports_added_bank_accounts' => 0,
                'active_reports_added_transactions' => 0,
            ];

            foreach ($user->getClients() as $client) {
                $row['client_fullname'] = $client->getFirstname() . ' ' . $client->getLastname();
                $row['client_casenumber'] = $client->getCaseNumber();
                foreach ($client->getReports() as $report) {
                    ++$row['total_reports'];
                    if ($report->getSubmitted()) {
                        continue;
                    }
                    ++$row['active_reports'];
                    if ($report->isDue()) {
                        ++$row['active_reports_due'];
                        $row['active_reports_added_bank_accounts'] += count($report->getAccounts());
                        foreach ($report->getTransactions() as $transaction) {
                            if (!empty($transaction->getAmounts())) {
                                ++$row['active_reports_added_transactions'];
                            }
                        }
                    }
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
