<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Report\BankAccount;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('explanation', 'textarea', [
                'required' => true,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'gifts.amount.type',
            ]);

            if (!empty($options['report']->getBankAccountOptions()) && $options['report']->getType() == '102') {
                $builder->add('bankAccountId', 'choice', [
                    'choices' => $options['report']->getBankAccountOptions(),
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
        ->setRequired(['user', 'report']);

    }

    public function getName()
    {
        return 'gifts_single';
    }
}
