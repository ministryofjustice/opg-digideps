<?php

namespace App\Service;

use App\Entity\PreRegistration;
use DateTime;

class ReportUtils
{
    /**
     * Generates and returns the report start date from a given end date.
     * -365 days + 1 if note a leap day (otherwise we get 2nd March).
     *
     * @return \DateTime $reportStartDate
     */
    public function generateReportStartDateFromEndDate(?\DateTime $reportEndDate)
    {
        $reportStartDate = clone $reportEndDate;

        $isLeapDay = '29-Feb' == $reportStartDate->format('d-M');
        $reportStartDate->sub(new \DateInterval('P1Y')); // One year behind end date
        if (!$isLeapDay) {
            $reportStartDate->add(new \DateInterval('P1D')); // + 1 day
        }

        return $reportStartDate;
    }

    /**
     * create DateTime object based on '16-Dec-2014' formatted dates
     * '16-Dec-14' format is accepted too, although seem deprecated according to latest given CSV files.
     *
     * @param string $dateString e.g. 16-Dec-2014
     * @param string $century    e.g. 20/19 Prefix added to 2-digits year
     *
     * @return \DateTime|null
     */
    public function parseCsvDate($dateString, $century)
    {
        $sep = '-';
        $pieces = explode($sep, $dateString);

        // prefix century if needed
        if (2 === strlen($pieces[2])) {
            $pieces[2] = ((string) $century).$pieces[2];
        }
        // check format is d-M-Y
        if ((int) $pieces[0] < 1 || (int) $pieces[0] > 31 || 3 !== strlen($pieces[1]) || 4 !== strlen($pieces[2])) {
            return null;
        }

        $ret = \DateTime::createFromFormat('d-M-Y', implode($sep, $pieces));
        if (!$ret instanceof \DateTime) {
            return null;
        }

        return $ret;
    }

    public function determineReportType(string $reportType, string $orderType, string $role)
    {
        if ('PA' == $role) {
            $realm = PreRegistration::REALM_PA;
        } elseif ('LAY' == $role) {
            $realm = PreRegistration::REALM_LAY;
        } else {
            $realm = PreRegistration::REALM_PROF;
        }

        return PreRegistration::getReportTypeByOrderType($reportType, $orderType, $realm);
    }

    public function padCasRecNumber(string $number)
    {
        return str_pad($number, 8, '0', STR_PAD_LEFT);
    }
}
