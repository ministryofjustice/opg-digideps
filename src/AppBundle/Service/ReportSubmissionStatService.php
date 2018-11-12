<?php

namespace AppBundle\Service;

use AppBundle\Entity\Ndr\Ndr;

class ReportSubmissionStatService
{
    /**
     * Generate all the report submissions as csv
     *
     * @param array $records
     *
     * @return string Csv content
     */
    public function generateReportSubmissionsCsvLines(array $records)
    {
        $ret = [[
            'id', 'report_type', 'deputy_no', 'email','name', 'lastname', 'registration_date', 'report_due_date', 'report_date_submitted',
            'last_logged_in', 'client_name', 'client_lastname', 'client_casenumber', 'client_court_order_date',
            'total_reports', 'active_reports'
        ]];

        foreach ($records as $rs) {
            $ret[] = $this->generateDataRow($rs);
        }

        return array_filter($ret);
    }


    /**
     * Takes an instance of ReportSubmission and ReportInterface and returns a summary of data relating to the
     * report submission. Returned to generate and sanitize the data to ensure CSV generation does not fail.
     *
     * @param array $rs
     *
     * @return array
     */
    private function generateDataRow(array $rs)
    {
        if (!empty($rs['report'])) {
            $reportOrNdr = $rs['report'];
            $reportType = $reportOrNdr['type'];
            $reportDueDate = $reportOrNdr['dueDate'];
        } else if (!empty($rs['ndr'])) {
            $reportOrNdr = $rs['ndr'];
            $reportType = 'ndr';
            $reportDueDate = Ndr::getDueDateBasedOnStartDate($reportOrNdr['startDate']);
        } else {
            return null;
        }

        $data = [];
        array_push($data, $rs['id']);
        array_push($data, $reportType);
        $createdBy = $rs['createdBy'] ;

        if ($createdBy) {
            array_push($data, $createdBy['deputyNo'] );
            array_push($data, $createdBy['email'] );
            array_push($data, $createdBy['firstname'] );
            array_push($data, $createdBy['lastname'] );
            array_push($data, $this->outputDate($createdBy['registrationDate'] ));
        } else {
            array_push($data, null);
            array_push($data, null);
            array_push($data, null);
            array_push($data, null);
            array_push($data, null);
        }

        array_push($data, $this->outputDate($reportDueDate));
        array_push($data, $this->outputDate($reportOrNdr['submitDate'] ));

        if ($createdBy) {
            array_push($data, $this->outputDate($createdBy['lastLoggedIn'] ));
        } else {
            array_push($data, null);
        }
        $client = $reportOrNdr['client'];
        array_push($data, $client['firstname'] );
        array_push($data, $client['lastname'] );
        array_push($data, $client['caseNumber'] );
        array_push($data, $this->outputDate($client['courtDate'] ));
        array_push($data, /*$client->getTotalReportCount()*/'n.a.');
        array_push($data, /*$client>getUnsubmittedReportsCount()*/'n.a.');

        return $data;
    }
    
    /**
     * Output a formatted string from a given \DateTime object if set.
     *
     * @param null|\DateTime $date
     * @return null|string
     */
    private function outputDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date->format('d/m/Y');
        }
        return null;
    }

}
