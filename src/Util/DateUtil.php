<?php

namespace DeployTracker\Util;

final class DateUtil
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
     * @param \DateTimeInterface $date
     * @return string
     */
    public static function formatFuzzy(\DateTimeInterface $date): string
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

    /**
     * @param int $timestamp
     * @return \DateTimeInterface
     */
    public static function createFromTimestamp(int $timestamp): \DateTimeInterface
    {
        return (new \DateTime())->setTimestamp($timestamp);
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return \DatePeriod
     */
    public static function createMonthDatePeriod(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): \DatePeriod {
        $startDate->modify('first day of this month');
        $endDate->modify('first day of next month');

        return self::createDatePeriod('1 month', $startDate, $endDate);
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return \DatePeriod
     */
    public static function createDayDatePeriod(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): \DatePeriod {
        $startDate->modify('midnight');
        $endDate->modify('+1 day')->modify('midnight')->modify('-1 second');

        return self::createDatePeriod('1 day', $startDate, $endDate);
    }

    /**
     * @param string $resolution
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return \DatePeriod
     */
    public static function createDatePeriod(
        string $resolution,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): \DatePeriod {
        $interval = \DateInterval::createFromDateString($resolution);

        return new \DatePeriod($startDate, $interval, $endDate);
    }
}
