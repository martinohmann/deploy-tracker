<?php

namespace DeployTracker\Tests\Util;

use PHPUnit\Framework\TestCase;
use DeployTracker\Util\DateUtil;

class DateUtilTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCreateDateFromTimestamp()
    {
        $timestamp = 1234567890;

        $date = DateUtil::createFromTimestamp($timestamp);

        self::assertSame($timestamp, $date->getTimestamp());
    }

    /**
     * @test
     * @dataProvider fuzzyDateProvider
     */
    public function shouldProduceCorrectFuzzyDate($given, $expected)
    {
        $date = new \DateTime($given);

        self::assertSame($expected, DateUtil::formatFuzzy($date));
    }

    /**
     * @return array
     */
    public function fuzzyDateProvider(): array
    {
        return [
            ['-1 year', '1 year ago'],
            ['-55 months', '5 years ago'],
            ['-60 days', '2 months ago'],
            ['-30 days', '1 month ago'],
            ['-29 days', '29 days ago'],
            ['-5 min', '5 minutes ago'],
            ['-10 hours', '10 hours ago'],
            ['+5 min', 'in 5 minutes'],
            ['+7 days', 'in 7 days'],
            ['+7 days', 'in 7 days'],
            ['+29 days', 'in 29 days'],
            ['+30 days', 'in 1 month'],
            ['+23 years', 'in 23 years'],
        ];
    }

    /**
     * @test
     * @dataProvider monthsDateProvider
     */
    public function shouldProduceCorrectMonthlyDatePeriod($start, $end, $expectedMonths)
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);

        $months = DateUtil::createMonthDatePeriod($startDate, $endDate);

        self::assertCount(count($expectedMonths), $months);

        foreach ($months as $month) {
            self::assertContains($month->format('Y-m'), $expectedMonths);
        }
    }

    /**
     * @return array
     */
    public function monthsDateProvider(): array
    {
        return [
            ['2017-10-06', '2018-02-19', ['2017-10', '2017-11', '2017-12', '2018-01', '2018-02']],
            ['2017-10-20', '2018-02-19', ['2017-10', '2017-11', '2017-12', '2018-01', '2018-02']],
            ['2017-10-20', '2017-12-31', ['2017-10', '2017-11', '2017-12']],
            ['2017-10-31', '2017-12-31', ['2017-10', '2017-11', '2017-12']],
            ['2017-10-31', '2017-12-01', ['2017-10', '2017-11', '2017-12']],
            ['2017-10-31', '2017-10-01', ['2017-10']],
            ['2017-10-31', '2017-09-01', []],
        ];
    }

    /**
     * @test
     * @dataProvider daysDateProvider
     */
    public function shouldProduceCorrectDailyDatePeriod($start, $end, $expectedDays)
    {
        $startDate = new \DateTime($start);
        $endDate = new \DateTime($end);

        $days = DateUtil::createDayDatePeriod($startDate, $endDate);

        self::assertCount(count($expectedDays), $days);

        foreach ($days as $day) {
            self::assertContains($day->format('Y-m-d'), $expectedDays);
        }
    }

    /**
     * @return array
     */
    public function daysDateProvider(): array
    {
        return [
            ['2017-10-06 00:00:00', '2017-10-07 00:00:00', ['2017-10-06', '2017-10-07']],
            ['2017-10-06 02:00:00', '2017-10-07 00:00:00', ['2017-10-06', '2017-10-07']],
            ['2017-10-06 02:00:00', '2017-10-07 23:40:00', ['2017-10-06', '2017-10-07']],
            ['2017-10-06 02:00:00', '2017-10-08 23:40:00', ['2017-10-06', '2017-10-07', '2017-10-08']],
            ['2017-10-07 02:00:00', '2017-10-06 23:40:00', []],
            ['2017-10-07 02:00:00', '2017-10-07 23:59:59', ['2017-10-07']],
            ['2017-10-07 10:00:00', '2017-10-07 09:40:00', ['2017-10-07']],
        ];
    }
}
