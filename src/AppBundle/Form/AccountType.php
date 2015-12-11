<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use Symfony\Component\Validator\Constraints as Assert;

class AccountType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('accountType', 'text');
        $builder->add('bank', 'text');
        $builder->add('accountNumber', 'number', ['max_length' => 4]);
        $builder->add('sortCode', new SortCodeType(), [ 'error_bubbling' => false]);

        $builder->add('openingBalance', 'number', [
            'precision' => 2,
            'invalid_message' => 'account.openingBalance.type',
            
        ]);
        $builder->add('closingBalance', 'number', [
            'precision' => 2,
            'invalid_message' => 'account.closingBalance.type'
        ]);

        $builder->add('save', 'submit');

    }
    
    public function getName()
    {
        return 'account';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults( [
            'translation_domain' => 'report-account-form',
            'validation_groups' => ['add_edit'],
        ]);
    }
}
