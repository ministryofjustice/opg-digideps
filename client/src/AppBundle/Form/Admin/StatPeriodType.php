<?php

namespace AppBundle\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatPeriodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period', FormTypes\ChoiceType::class, [
                'choices' => array_flip([
                    'last-30' => 'Last 30 days',
                    'this-year' => 'This year',
                    'all-time' => 'All time',
                    'custom' => 'Custom',
                ]),
                'mapped' => false,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('startDate', FormTypes\DateType::class)
            ->add('endDate', FormTypes\DateType::class)
            ->add('update', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'admin-metrics',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'admin';
    }
}
