<?php

namespace AppBundle\Form\Report;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ProfDeputyManagementCostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profDeputyManagementCostTypeId', FormTypes\HiddenType::class)
            ->add('amount', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'error_bubbling' => false,
                'invalid_message' => 'profDeputyManagementCost.amount.notNumeric',
            ]);
    }
}