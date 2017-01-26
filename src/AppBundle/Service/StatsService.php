<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\CasRec;
use Doctrine\ORM\EntityRepository;

class StatsService
{
    /** @var  EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->userRepository = $em->getRepository(User::class);
        $this->reportRepository = $em->getRepository(Report::class);
    }

    /**
     * @param integer $maxResults
     *
     * @return array
     */
    public function getRecords($maxResults = null)
    {
        $ret = [];
        $qb = $this->userRepository->createQueryBuilder('u');
        $qb->leftJoin('u.role', 'r')
            ->leftJoin('u.clients', 'c')
            ->where('r.role = ?1')
            ->orderBy('u.id', 'DESC');
        $qb->setParameter('1', 'ROLE_LAY_DEPUTY');

        if ($maxResults) {
            $qb->setMaxResults($maxResults);
        }
        $query = $qb->getQuery();

        $users = $query->getResult();

        foreach ($users as $user) {
            /* @var $user User */
            $row = [
                'id'                      => $user->getId(),
                'email'                   => $user->getEmail(),
                'name'                    => $user->getFirstname(),
                'lastname'                => $user->getLastname(),
                'registration_date'       => $user->getRegistrationDate() ? $user->getRegistrationDate()->format('Y-m-d') : '-',
                'report_date_due'         => 'n/a',
                'report_date_submitted'   => 'n/a',
                'last_logged_in'          => $user->getLastLoggedIn() ? $user->getLastLoggedIn()->format('Y-m-d H:i:s') : '-',
                'client_name'             => 'n.a.',
                'client_lastname'         => 'n.a.',
                'client_casenumber'       => 'n.a.',
                'client_court_order_date' => 'n.a.',
                'total_reports'           => 0,
                'active_reports'          => 0,
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
            $activeReportId = $user->getActiveReportId();
            if ($activeReportId) {
                $report = $this->reportRepository->find($activeReportId);
                $row['report_date_due'] = $report->getDueDate()->format('Y-m-d');
            }

            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * @param integer $maxResults
     *
     * @return string
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
