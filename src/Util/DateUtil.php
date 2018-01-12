<?php

namespace DeployTracker\Util;

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

    const FMT_FUZZY_AGO = '%s %s%s ago';
    const FMT_FUZZY_IN = 'in %s %s%s';

    /**
     * @param \DateTime $date
     * @return string
     */
    public static function formatFuzzy(\DateTime $date): string
    {
        $format = self::FMT_FUZZY_AGO;
        $dateDiff = time() - $date->getTimestamp();

        if ($dateDiff < 0) {
            $format = self::FMT_FUZZY_IN;
        }

        $dateDiff = abs($dateDiff);

        if ($dateDiff < 1) {
            return sprintf($format, 'less than 1', 'second');
        }

        foreach (self::SECONDS_TO_TIME_PERIOD as $seconds => $period) {
            $d = $dateDiff / $seconds;

            if ($d >= 1) {
                $rounded = round($d);
                return sprintf(
                    $format,
                    $rounded,
                    $period,
                    $rounded > 1 ? 's' : ''
                );
            }
        }
    }
}
