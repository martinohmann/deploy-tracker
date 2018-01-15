<?php

namespace DeployTracker\Tests\Form\Util;

use PHPUnit\Framework\TestCase;
use Phake;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use DeployTracker\Form\Util\FormErrorCollector;

class FormErrorCollectorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCollectAllErrorsFromParentAndChildren()
    {
        $childForm = Phake::mock(FormInterface::class);
        $childFormError = new FormError('child form error');

        Phake::when($childForm)->getErrors->thenReturn([$childFormError]);
        Phake::when($childForm)->all->thenReturn([]);

        $form = Phake::mock(FormInterface::class);
        $formError = new FormError('parent form error');

        Phake::when($form)->getErrors->thenReturn([$formError]);
        Phake::when($form)->all->thenReturn([$childForm]);

        $collector = new FormErrorCollector();
        $errors = $collector->collectErrors($form);

        self::assertCount(2, $errors);
    }
}
