<?php

namespace App\Form\Admin\Fixture;

use App\Entity\Report\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrgCourtOrderFixtureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deputyType', ChoiceType::class, [
                'choices' => [
                    'Public Authority (Named)' => 'PA',
                    'Public Authority (Admin)' => 'PA_ADMIN',
                    'Public Authority (Team Member)' => 'PA_TEAM_MEMBER',
                    'Professional (Named)' => 'PROF',
                    'Professional (Admin)' => 'PROF_ADMIN',
                    'Professional (Team Member)' => 'PROF_TEAM_MEMBER',
                ],
                'data' => $options['deputyType'],
            ])
            ->add('reportType', ChoiceType::class, [
                'choices' => [
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
            ->add('activated', ChoiceType::class, [
                'choices' => ['Activated' => true, 'Not Activated' => false],
                'data' => $options['activated'],
            ])
            ->add('orgSizeClients', ChoiceType::class, [
                'choices' => ['1 Client' => 1, '10 Clients' => 10, '100 Clients' => 100, '500 Clients' => 500, '1000 Clients' => 1000],
                'data' => $options['orgSizeClients'],
            ])
            ->add('orgSizeUsers', ChoiceType::class, [
                'choices' => ['1 User' => 1, '10 Users' => 10, '50 Users' => 50, '100 Users' => 100, '150 Users' => 150],
                'data' => $options['orgSizeUsers'],
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-fixtures',
        ])->setRequired(['deputyType', 'reportType', 'reportStatus', 'activated', 'orgSizeClients', 'orgSizeUsers']);
    }
}
