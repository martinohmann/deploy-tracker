<?php

namespace DeployTracker\Renderer;

use DeployTracker\Histogram\DateHistogram;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;

class C3JsDateHistogramScriptRenderer
{
    /**
     * @param string $bindTo
     * @param DateHistogram $histogram
     * @param string $dateFormat
     * @return string
     */
    public function render(string $bindTo, DateHistogram $histogram, string $dateFormat = '%Y-%m'): string
    {
        $histogramJson = json_encode($histogram);

        $script = <<<EOS
            <script type="text/javascript">
                c3.generate({
                  bindto: '#$bindTo',
                  data: {
                    x: 'x',
                    xFormat: '$dateFormat',
                    columns: $histogramJson,
                  },
                  padding: {
                    top: 20,
                    right: 30,
                    bottom: 0,
                  },
                  legend: {
                    position: 'bottom',
                  },
                  point: {
                    show: false
                  },
                  axis: {
                    x: {
                      type: 'timeseries',
                      tick: {
                        format: '$dateFormat',
                      },
                      padding: {
                        left: 0,
                        right: 0
                      },
                    },
                    y: {
                      padding: {
                        bottom: 0
                      },
                    },
                  },
                  grid: {
                    x: {
                      show: true
                    },
                    y: {
                      show: true
                    }
                  }
                });
            </script>
EOS;
        return $script;
    }
}
