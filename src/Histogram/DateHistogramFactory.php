<?php

namespace DeployTracker\Histogram;

use DeployTracker\Repository\DeploymentRepository;
use DeployTracker\Util\DateUtil;

class DateHistogramFactory
{
    /**
     * @param DeploymentRepository $repository
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return DateHistogram
     */
    public function createDailyDeploymentHistogram(
        DeploymentRepository $repository,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): DateHistogram {
        $period = DateUtil::createDayDatePeriod($startDate, $endDate);

        return new DateHistogram(
            $period,
            $repository->getDailyCountsInPeriod($period),
            'Y-m-d'
        );
    }

    /**
     * @param DeploymentRepository $repository
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return DateHistogram
     */
    public function createMonthlyDeploymentHistogram(
        DeploymentRepository $repository,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): DateHistogram {
        $period = DateUtil::createMonthDatePeriod($startDate, $endDate);

        return new DateHistogram(
            $period,
            $repository->getMonthlyCountsInPeriod($period),
            'Y-m'
        );
    }
}
