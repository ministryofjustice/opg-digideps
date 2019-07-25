<?php

namespace AppBundle\Service;

class ReportUtils
{
    /**
     * Generates and returns the report start date from a given end date.
     * -365 days + 1 if note a leap day (otherwise we get 2nd March)
     *
     * @param \DateTime $reportEndDate
     *
     * @return \DateTime $reportStartDate
     */
    public static function generateReportStartDateFromEndDate(\DateTime $reportEndDate)
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
     * @return \DateTime|false
     */
    public static function parseCsvDate($dateString, $century)
    {
        $sep = '-';
        //$errorMessage = "Can't recognise format for date $dateString. expected d-M-Y or d-M-y e.g. 05-MAR-2005 or 05-MAR-05";
        $pieces = explode($sep, $dateString);

        // prefix century if needed
        if (strlen($pieces[2]) === 2) {
            $pieces[2] = ((string) $century) . $pieces[2];
        }
        // check format is d-M-Y
        if ((int) $pieces[0] < 1 || (int) $pieces[0] > 31 || strlen($pieces[1]) !== 3 || strlen($pieces[2]) !== 4) {
            return false;
            //throw new \InvalidArgumentException($errorMessage);
        }

        $ret = \DateTime::createFromFormat('d-M-Y', implode($sep, $pieces));
        if (!$ret instanceof \DateTime) {
            return false;
            //throw new \InvalidArgumentException($errorMessage);
        }

        return $ret;
    }
}
