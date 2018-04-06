<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Report\BankAccount;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $banks = [];
        foreach ($options['banks'] as $bank) {
            /* @var $bank BankAccount */
            $banks[$bank->getId()] = (!empty($bank->getBank()) ? $bank->getBank() . ' - '  : '') . $bank->getAccountTypeText() . ' (****' . $bank->getAccountNumber() . ')';
        }

        $builder
            ->add('explanation', 'textarea', [
                'required' => true,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'gifts.amount.type',
            ]);

            if (!empty($banks)) {
                $builder->add('fromAccount', 'choice', [
                    'choices' => $banks,
                    'empty_value' => 'Please select'
                ]);
            }

           $builder ->add('save', 'submit');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Gift::class,
            'validation_groups' => ['gift'],
            'translation_domain' => 'report-gifts',
        ])
        ->setRequired(['banks'])
        ->setAllowedTypes('banks', 'array');

    }

    public function getName()
    {
        return 'gifts_single';
    }
}
