<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;

class ReportUtils
{
    /**
     * Generates and returns the report start date from a given end date.
     * -365 days + 1 if note a leap day (otherwise we get 2nd March)
     *
     * @param \DateTime|null $reportEndDate
     *
     * @return \DateTime $reportStartDate
     */
    public function generateReportStartDateFromEndDate(?\DateTime $reportEndDate)
    {
        $reportStartDate = clone $reportEndDate;

        $isLeapDay = $reportStartDate->format('d-M') == '29-Feb';
        $reportStartDate->sub(new \DateInterval('P1Y')); // One year behind end date
        if (!$isLeapDay) {
            $reportStartDate->add(new \DateInterval('P1D')); // + 1 day
        }

        return $reportStartDate;
    }

    /**
     * create DateTime object based on '16-Dec-2014' formatted dates
     * '16-Dec-14' format is accepted too, although seem deprecated according to latest given CSV files
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
        if (strlen($pieces[2]) === 2) {
            $pieces[2] = ((string) $century) . $pieces[2];
        }
        // check format is d-M-Y
        if ((int) $pieces[0] < 1 || (int) $pieces[0] > 31 || strlen($pieces[1]) !== 3 || strlen($pieces[2]) !== 4) {
            return null;
        }

        $ret = \DateTime::createFromFormat('d-M-Y', implode($sep, $pieces));
        if (!$ret instanceof \DateTime) {
            return null;
        }

        return $ret;
    }

    public function convertTypeofRepAndCorrefToReportType(string $typeOfRep, string $corref, string $realm)
    {
        return CasRec::getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref, $realm);
    }

    public function padCasRecNumber(string $number)
    {
        return str_pad($number, 8, '0', STR_PAD_LEFT);
    }
}
