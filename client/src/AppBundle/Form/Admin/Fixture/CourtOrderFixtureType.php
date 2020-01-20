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
                'choices' => ['Lay' => User::TYPE_LAY, 'PA' => User::TYPE_PA, 'Prof' => User::TYPE_PROF],
                'data' => $options['deputyType']
            ])
            ->add('reportType', ChoiceType::class, [
                'choices' => [
                    'Health and Welfare' => Report::TYPE_HEALTH_WELFARE,
                    'Property and financial affairs low assets' => Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS,
                    'Property and financial affairs high assets' => Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS,
                    'Low assets with health and welfare' => Report::TYPE_COMBINED_LOW_ASSETS,
                    'High assets with health and welfare' => Report::TYPE_COMBINED_HIGH_ASSETS,
                ],
                'data' => $options['reportType']
            ])
            ->add('reportStatus', ChoiceType::class, [
                'choices' => ['Not started' => Report::STATUS_NOT_STARTED, 'Submitted' => Report::STATUS_SUBMITTED],
                'data' => $options['reportStatus']
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-fixtures'
        ])->setRequired(['deputyType', 'reportType', 'reportStatus']);
    }
}
