<?php

namespace AppBundle\Form\Report;


use AppBundle\Entity\Report\ProfDeputyManagementCost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfDeputyManagementCostSingleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyManagementCostTypeIds', FormTypes\HiddenType::class)
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false,
                'invalid_message' => 'profDeputyManagementCost.amount.notNumeric',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProfDeputyManagementCost::class,
            'validation_groups' => ['prof-deputy-management-costs'],
            'translation_domain' => 'report-prof-deputy-management-costs',
        ]);
    }
}