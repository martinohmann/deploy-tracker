<?php

namespace DeployTracker\Twig;

class SearchExtension extends \Twig_Extension
{
    const HIGHLIGHT_PREFIX = '<span class="text-dark bg-warning">';
    const HIGHLIGHT_SUFFIX = '</span>';

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'highlight',
                [$this, 'highlight'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string $content
     * @param string $subject
     * @return string
     */
    public function highlight(string $content, string $subject): string
    {
        $replacement = sprintf('%s%s%s', self::HIGHLIGHT_PREFIX, $subject, self::HIGHLIGHT_SUFFIX);

        return str_replace($subject, $replacement, $content);
    }
}
