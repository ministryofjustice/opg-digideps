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

        $hoursString = $hours.' '.((1 === $hours) ? 'hour' : 'hours');
        $minutesString = $minutes.' '.((1 === $minutes) ? 'minute' : 'minutes');

        // less than a minute
        if (0 === $hours && 0 === $minutes) {
            return 'less than a minute';
        }

        // X minutes
        if (0 === $hours) {
            return $minutesString;
        }

        // X hours
        if (0 === $minutes) {
            return $hoursString;
        }

        // X hours and Y minutes
        return $hoursString.' and '.$minutesString;
    }

    /**
     * Implodes strings, but with a different joiner between the last two.
     */
    public static function implodeWithDifferentLast(array $strings, string $joiner, string $lastJoiner)
    {
        $last = array_pop($strings);

        if ($strings) {
            return implode($joiner, $strings).$lastJoiner.$last;
        }

        return $last;
    }

    /**
     * Replaces all returns with standard \n, removes tabs and extra spaces and any excess new lines.
     */
    public static function cleanText(string $input): string
    {
        $input = trim($input);
        $input = str_replace(["\r\n", "\r"], "\n", $input);
        $input = preg_replace("/[ \t]+/", ' ', $input) ?? $input;
        $input = preg_replace("/\n{2,}/", "\n", $input) ?? $input;

        return $input;
    }
}
