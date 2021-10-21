<?php

declare(strict_types=1);

namespace App\Form\Report;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncomeReceivedOnClientsBehalfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('incomeType', TextType::class, ['required' => true]);
        $builder->add('amount', NumberType::class, [
                'required' => false,
                'invalid_message' => 'The amount value must be in numbers',
            ]
        );

        $builder->add('amountDontKnow', CheckboxType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IncomeReceivedOnClientsBehalf::class,
            'allow_add' => true,
            'allow_extra_fields' => true,
            'validation_groups' => ['client-benefits-check', 'client-benefits-check'],
        ]);
    }
}
