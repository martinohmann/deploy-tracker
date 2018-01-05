<?php

namespace Lesara\DeployTracker\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PublishRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('application', TextType::class)
            ->add('project_url', TextType::class)
            ->add('stage', TextType::class)
            ->add('branch', TextType::class)
            ->add('commit_hash', TextType::class)
            ->add('deployer', TextType::class)
            ->add('timestamp', IntegerType::class, ['empty_data' => (string) time()])
            ->add('status', TextType::class);
    }
}
