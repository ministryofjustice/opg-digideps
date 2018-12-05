<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyShortCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyCostPreviousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
            ])
            ->add('endDate', FormTypes\DateType::class, ['widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false,
//                'invalid_message' => 'TODO.amount.notNumeric',
            ])

            ->add('saveAndAddAnother', FormTypes\SubmitType::class, ['label' => 'save.label'])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
             'translation_domain' => 'report-prof-deputy-costs',
             'validation_groups' => ['prof-deputy-prev-costs'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'deputy_costs_previous';
    }
}
