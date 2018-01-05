<?php

namespace Lesara\DeployTracker\Twig;

use Lesara\DeployTracker\Util\DateUtil;

class DateExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('fuzzy_date', [DateUtil::class, 'getFuzzyDate']),
        ];
    }
}
