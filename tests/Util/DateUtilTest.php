<?php

namespace DeployTracker\Tests\Util;

use PHPUnit\Framework\TestCase;
use DeployTracker\Util\DateUtil;

class DateUtilTest extends TestCase
{
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
            ['+1 month', 'in 1 month'],
            ['+29 days', 'in 29 days'],
            ['+30 days', 'in 1 month'],
            ['+23 years', 'in 23 years'],
        ];
    }
}
