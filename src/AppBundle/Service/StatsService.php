<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\CasRec;

class StatsService
{
    /** @var EntityManager */
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * @param integer $maxResults
     *
     * @return array
     */
    public function getRecords($maxResults = null)
    {
        //$deputy = $this->getRepository('Role')->findBy(['role'=>'ROLE_LAY_DEPUTY']);
        // pre-join data to reduce number of queries
//         $users = $this->getRepository('User')->findBy(['role'=>$deputy], ['id' => 'DESC']);
        $qb = $this->em->createQuery(
            "SELECT u, c, role FROM AppBundle\Entity\User u
                LEFT JOIN u.role role
                LEFT JOIN u.clients c
                WHERE role.role = 'ROLE_LAY_DEPUTY' ORDER BY u.id DESC" // 87M
        );

        if ($maxResults) {
            $qb->setMaxResults($maxResults);
        }

        $users = $qb->getResult();

        // alternative without join and lazy-loading
        // $deputy = $this->getRepository('Role')->findBy(['role'=>'ROLE_LAY_DEPUTY']);
        // $users = $this->getRepository('User')->findBy(['role'=>$deputy], ['id' => 'DESC']);

        foreach ($users as $user) {
            /* @var $user User */
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

        return $ret;
    }

    /**
     * @param integer $maxResults
     *
     * @return array
     */
    public function getRecordsCsv($maxResults = null)
    {
        $records = $this->getRecords($maxResults);

        $out = fopen('php://memory', 'w');
        fputcsv($out, array_keys($records[0]));
        foreach ($records as $row) {
            fputcsv($out, $row);
        }
        rewind($out);

        return stream_get_contents($out);
    }
}
