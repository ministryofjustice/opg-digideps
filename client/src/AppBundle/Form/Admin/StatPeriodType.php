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
                'choices' => [
                    'last-30'=> 'last-30',
                    'this-year'=> 'this-year',
                    'all-time'=> 'all-time',
                    'custom'=> 'custom',
                ],
                'choice_label' => function ($choice) {
                    return 'form.period.options.' . $choice;
                },
                'expanded' => true,
                'multiple' => false,
                'data' => 'last-30'
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
