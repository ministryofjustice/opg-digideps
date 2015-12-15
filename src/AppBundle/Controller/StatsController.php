<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Exception as AppExceptions;
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
        $this->denyAccessUnlessGranted(EntityDir\Role::ADMIN);

        $rows = $this->getQueryResults(
            'SELECT
            u.id as user_id, u.email, u.firstname, u.lastname,
            u.registration_date as created_at,
            u.active as is_active,
            length(u.address1)>0 as has_details
            FROM dd_user u
            WHERE u.role_id=2
            ORDER BY u.id DESC;');

        // add data
        foreach ($rows as &$row) {
            $userId = $row['user_id'];

            // reports
            $reports = $this->getQueryResults(
                'SELECT * from report r
                LEFT JOIN client c ON r.client_id = c.id
                LEFT JOIN deputy_case dc ON dc.client_id = ' . $userId);

            $reportsSubmitted = array_filter($reports, function ($report) {
                return $report['submitted'];
            });
            $reportsUnsubmitted = array_filter($reports, function ($report) {
                return !$report['submitted'];
            });

            $row['reports_unsubmitted'] = count($reportsUnsubmitted);
            $row['reports_submitted'] = count($reportsSubmitted);

            // bank accounts
            foreach ($reportsUnsubmitted as $reportId) {
                $banks = $this->getQueryResults(
                    'SELECT a.bank_name, COUNT(at.id)
                    FROM account a
                    LEFT JOIN account_transaction at ON at.account_id = a.id
                    WHERE a.report_id = ' . $reportId['id'] . '
                    AND at.amount IS NOT NULL
                    GROUP BY (a.id)');
            }
            $row['reports_unsubmitted_bank_accounts'] = count($banks);
            $row['reports_unsubmitted_completed_transactions'] = array_sum(array_map(function ($b) {
                return $b['count'];
            }, $banks));

        }

        return $rows;
    }

    /**
     * @param $sql
     *
     * @return array
     */
    private function getQueryResults($sql)
    {
        $connection = $this->get('em')->getConnection();

        return $connection->query($sql)->fetchAll();
    }


}
