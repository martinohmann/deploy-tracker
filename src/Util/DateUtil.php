<?php

namespace Lesara\DeployTracker\Util;

class DateUtil
{
    const SECONDS_TO_TIME_PERIOD = [
        31104000 => 'year',
        2592000 => 'month',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];

    /**
     * @param \DateTime $date
     * @return string
     */
    public static function getFuzzyDate(\DateTime $date): string
    {
        $dateDiff = time() - $date->getTimestamp();

        if ($dateDiff < 1) {
            return 'less than 1 second ago';
        }

        foreach (self::SECONDS_TO_TIME_PERIOD as $seconds => $period) {
            $d = $dateDiff / $seconds;

            if ($d >= 1) {
                $rounded = round($d);
                return sprintf(
                    '%d %s%s ago',
                    $rounded,
                    $period,
                    $rounded > 1 ? 's' : ''
                );
            }
        }
    }
}
