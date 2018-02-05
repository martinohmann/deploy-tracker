<?php

namespace DeployTracker\Histogram;

class DateHistogram implements \JsonSerializable
{
    /**
     * @var array
     */
    private $viewData;

    /**
     * @var array
     */
    private $xLabels = ['x'];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param \DatePeriod $datePeriod
     * @param iterable $rawData
     * @param string $dateFormat
     */
    public function __construct(\DatePeriod $datePeriod, iterable $rawData, string $dateFormat = 'Y-m')
    {
        $this->compile($datePeriod, $rawData, $dateFormat);
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->getViewData();
    }

    /**
     * @return bool
     */
    public function hasData(): bool
    {
        return count($this->data) > 0;
    }

    /**
     * @return array
     */
    public function getViewData(): array
    {
        return $this->viewData;
    }

    /**
     * @param \DatePeriod $datePeriod
     * @param iterable $rawData
     * @param string $dateFormat
     * @return void
     */
    private function compile(\DatePeriod $datePeriod, iterable $rawData, string $dateFormat): void
    {
        foreach ($datePeriod as $dt) {
            $this->xLabels[] = $dt->format($dateFormat);
        }

        $dataPoints = count($this->xLabels);

        foreach ($rawData as $dataPoint) {
            $label = $dataPoint['label'];

            if (!isset($this->data[$label])) {
                $this->data[$label] = array_fill(0, $dataPoints, 0);
                $this->data[$label][0] = $label;
            }

            if (false !== ($key = array_search($dataPoint['date'], $this->xLabels))) {
                $this->data[$label][$key] = (int) $dataPoint['count'];
            }
        }

        $this->data = array_values($this->data);
        $this->viewData = array_merge([$this->xLabels], $this->data);
    }
}
