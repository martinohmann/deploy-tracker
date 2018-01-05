<?php

namespace Lesara\DeployTracker\Form\Util;

use Symfony\Component\Form\FormInterface;

class FormErrorCollector
{
    /**
     * @param FormInterface $form
     * @return array
     */
    public function collectErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->collectErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
