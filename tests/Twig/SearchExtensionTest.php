<?php

namespace DeployTracker\Tests\Twig;

use PHPUnit\Framework\TestCase;
use DeployTracker\Twig\SearchExtension;

class SearchExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldWrapSubjectWithHtml()
    {
        $extension = new SearchExtension();

        $highlighted = $extension->highlight('this text should be highlighted', 'high');

        $expected = sprintf(
            'this text should be %shigh%slighted',
            SearchExtension::HIGHLIGHT_PREFIX,
            SearchExtension::HIGHLIGHT_SUFFIX
        );

        self::assertSame($expected, $highlighted);
    }

    /**
     * @test
     */
    public function shouldReturnInitialStringIfThereIsNothingToHighlight()
    {
        $extension = new SearchExtension();

        $content = 'nothing will be highlighted';

        $highlighted = $extension->highlight($content, 'everything');

        self::assertSame($content, $highlighted);
    }
}
