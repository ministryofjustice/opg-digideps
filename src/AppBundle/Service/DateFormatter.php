<?php
namespace AppBundle\Service;

use AppBundle\Service\ApiClient;
use AppBundle\Entity\Client;

class DateFormatter
{
    public static function formatLastLogin(\DateTime $date, \DateTime $currentDate)
    {
        $secondsDiff = $currentDate->getTimestamp() - $date->getTimestamp();
        
        if ($secondsDiff < 60) {
            return 'less than a minute ago';
        }
        
        if ($secondsDiff < 3600) {
            $minutes = (int)round($secondsDiff / 60, 0);
            if ($minutes === 1) {
                return '1 minute ago';
            } else {
                return $minutes . ' minutes ago';
            }
        }
        
        if ($secondsDiff < 3600 * 24) {
            $hours = (int)round($secondsDiff / 3600, 0);
            if ($hours === 1) {
                return '1 hour ago';
            } else {
                return $hours . ' hours ago';
            }
        }
        
        return $date->format('d/m/Y');
        
    }
}