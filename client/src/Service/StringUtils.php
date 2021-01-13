<?php

namespace App\Service;

class StringUtils
{
    /**
     * Transforms 3900 into "1 hour and 5 minutes".
     *
     * @param int $seconds
     *
     * @return string
     */
    public static function secondsToHoursMinutes($seconds)
    {
        $hours = intval(gmdate('H', $seconds));
        $minutes = intval(gmdate('i', $seconds));

        $hoursString = $hours . ' ' . (($hours === 1) ? 'hour' : 'hours');
        $minutesString = $minutes . ' ' . (($minutes === 1) ? 'minute' : 'minutes');

        // less than a minute
        if ($hours === 0 && $minutes === 0) {
            return 'less than a minute';
        }

        // X minutes
        if ($hours === 0) {
            return $minutesString;
        }

        // X hours
        if ($minutes === 0) {
            return $hoursString;
        }

        // X hours and Y minutes
        return $hoursString . ' and ' . $minutesString;
    }

    /**
     * Implodes strings, but with a different joiner between the last two
     */
    public static function implodeWithDifferentLast(array $strings, string $joiner, string $lastJoiner)
    {
        $last = array_pop($strings);

        if ($strings) {
            return implode($joiner, $strings) . $lastJoiner . $last;
        }

        return $last;
    }
}
