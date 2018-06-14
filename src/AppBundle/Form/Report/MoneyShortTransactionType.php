<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\MoneyTransactionShort;
use Symfony\Component\Form\AbstractType; use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyShortTransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', FormTypes\TextType::class, [
                'required' => true,
            ])
            ->add('amount', FormTypes\NumberType::class, [
                'precision' => 2,
                'grouping'  => true,
            ])
            ->add('date', FormTypes\DateType::class, ['widget'          => 'text',
                                            'input'           => 'datetime',
                                            'format'          => 'dd-MM-yyyy',
                                            'invalid_message' => 'Enter a valid date',
            ])
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MoneyTransactionShort::class,
            'validation_groups'  => ['money-transaction-short'],
            'translation_domain' => 'report-money-short',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'money_short_transaction';
    }
}
