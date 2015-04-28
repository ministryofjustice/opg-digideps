<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Form\Type\AccountNumberType;
use Symfony\Component\Form\FormInterface;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bank', 'text')
            ->add('openingDate', 'date', [ 'widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'account.openingDate.invalidMessage'
            ])
            ->add('openingBalance', 'number', [ 'grouping' => true, 'precision' => 2])
            ->add('sortCode', new SortCodeType(), [ 'error_bubbling' => false])
            ->add('accountNumber', new AccountNumberType(), [ 'error_bubbling' => false]);

        
        if (!empty($options['addClosingBalance'])) {

            $builder->add('closingDate', 'date', [ 'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Value or character is not valid'
                ])
                ->add('closingBalance', 'number', [ 'grouping' => true, 'precision' => 2]);
        }

        $builder->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-accounts',
            'data_class' => 'AppBundle\Entity\Account',
            'addClosingBalance' => false,
            'validation_groups' => function(FormInterface $form) { 
                $account = $form->getData();
                $reportIsDue = $account->getReportObject()->isDue();
                
                return $reportIsDue ? ['basic', 'balance'] : ['basic'];
            }
        ]);
    }

    public function getName()
    {
        return 'account';
    }

}