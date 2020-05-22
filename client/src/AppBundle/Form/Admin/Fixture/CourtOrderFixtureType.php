<?php

namespace AppBundle\Form\Admin\Fixture;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourtOrderFixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deputyType', ChoiceType::class, [
                'choices' => ['Lay' => User::TYPE_LAY, 'Public Authority' => User::TYPE_PA, 'Professional' => User::TYPE_PROF],
                'data' => $options['deputyType']
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
                'data' => $options['reportType']
            ])
            ->add('reportStatus', ChoiceType::class, [
                'choices' => ['Not started' => Report::STATUS_NOT_STARTED, 'Submittable' => Report::STATUS_READY_TO_SUBMIT],
                'data' => $options['reportStatus']
            ])
            ->add('coDeputyEnabled', ChoiceType::class, [
                'choices' => ['Enabled' => true, 'Disabled' => false],
                'data' => $options['coDeputyEnabled']
            ])
            ->add('activated', ChoiceType::class, [
                'choices' => ['Activated' => true, 'Not Activated' => false],
                'data' => $options['activated']
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-fixtures'
        ])->setRequired(['deputyType', 'reportType', 'reportStatus', 'coDeputyEnabled', 'activated']);
    }
}
