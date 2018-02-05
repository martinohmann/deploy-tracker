<?php

namespace DeployTracker\Twig;

use DeployTracker\Histogram\DateHistogram;
use DeployTracker\Renderer\C3JsDateHistogramScriptRenderer;

class C3JsExtension extends \Twig_Extension
{
    /**
     * @var C3JsDateHistogramScriptRenderer
     */
    private $histogramRenderer;

    /**
     * @var array
     */
    private $histograms = [];

    /**
     * @param C3JsDateHistogramScriptRenderer $histogramRenderer
     */
    public function __construct(C3JsDateHistogramScriptRenderer $histogramRenderer)
    {
        $this->histogramRenderer = $histogramRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'c3_date_histogram',
                [$this, 'renderDateHistogramContainer'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'c3_scripts',
                [$this, 'renderScripts'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string $container
     * @param DateHistogram $histogram
     * @param string $dateFormat
     * @return string
     */
    public function renderDateHistogramContainer(
        string $container,
        DateHistogram $histogram,
        string $dateFormat = '%Y-%m'
    ): string {
        $this->histograms[$container] = ['data' => $histogram, 'dateFormat' => $dateFormat];

        return sprintf('<div id="%s"></div>', $container);
    }

    /**
     * @return string
     */
    public function renderScripts(): string
    {
        $renderedScripts = [];

        foreach ($this->histograms as $container => $histogram) {
            $renderedScripts[] = $this->histogramRenderer->render(
                $container,
                $histogram['data'],
                $histogram['dateFormat']
            );
        }

        return implode("\n", $renderedScripts);
    }
}
