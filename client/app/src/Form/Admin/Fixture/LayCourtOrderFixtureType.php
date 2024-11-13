<?php

namespace App\Form\Admin\Fixture;

use App\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayCourtOrderFixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deputyType', ChoiceType::class, [
                'choices' => [
                    'Lay' => 'LAY',
                ],
                'data' => $options['deputyType'],
            ])
            ->add('reportType', ChoiceType::class, [
                'choices' => [
                    'NDR' => 'ndr',
                    'Health and Welfare' => Report::TYPE_HEALTH_WELFARE,
                    'Property and financial affairs low assets' => Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
                    'Property and financial affairs high assets' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
                    'Low assets with health and welfare' => Report::TYPE_COMBINED_LOW_ASSETS,
                    'High assets with health and welfare' => Report::TYPE_COMBINED_HIGH_ASSETS,
                ],
                'data' => $options['reportType'],
            ])
            ->add('reportStatus', ChoiceType::class, [
                'choices' => ['Not started' => Report::STATUS_NOT_STARTED, 'Submittable' => Report::STATUS_READY_TO_SUBMIT],
                'data' => $options['reportStatus'],
            ])
            ->add('multiClientEnabled', ChoiceType::class, [
                'choices' => ['Enabled' => true, 'Disabled' => false],
                'data' => $options['multiClientEnabled'],
            ])
            ->add('coDeputyEnabled', ChoiceType::class, [
                'choices' => ['Enabled' => true, 'Disabled' => false],
                'data' => $options['coDeputyEnabled'],
            ])
            ->add('activated', ChoiceType::class, [
                'choices' => ['Activated' => true, 'Not Activated' => false],
                'data' => $options['activated'],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-fixtures',
        ])->setRequired(['deputyType', 'reportType', 'coDeputyEnabled', 'reportStatus', 'activated', 'multiClientEnabled']);
    }
}
