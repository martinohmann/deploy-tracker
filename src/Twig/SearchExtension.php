<?php

namespace DeployTracker\Twig;

class SearchExtension extends \Twig_Extension
{
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
        $replacement = sprintf('<span class="text-dark bg-warning">%s</span>', $subject);
        return str_replace($subject, $replacement, $content);
    }
}
