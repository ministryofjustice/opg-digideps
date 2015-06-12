<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Form\Type\AccountNumberType;
use Symfony\Component\Form\FormInterface;

/**
 * Form to add and edit account
 * supports ctor parameters to add additional elements (closing balance and delete button)
 */
class AccountType extends AbstractType
{
    /**
     * @var array 
     */
    private $options = [
        'showClosingBalance' => false,
        'showSubmitButton'   => true,
        'showDeleteButton'   => false,
    ];
    
    /**
     * @param boolean showClosingBalance, default false
     * @param boolean showSubmitButton, default true
     * @param boolean showDeleteButton, default false
     */
    public function __construct(array $optionsOverride = [])
    {
        $this->options = $optionsOverride +  $this->options;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bank', 'text')
            ->add('openingDate', 'date', [ 'widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'account.openingDate.invalidMessage'
            ])
            ->add('openingBalance', 'number', [ 'grouping' => true, 'precision' => 2])
            ->add('openingDateExplanation', 'textarea')
            ->add('sortCode', new SortCodeType(), [ 'error_bubbling' => false])
            ->add('accountNumber', new AccountNumberType(), [ 'error_bubbling' => false]);

        
        if ($this->options['showClosingBalance']) {
            $builder->add('closingDate', 'date', [ 'widget' => 'text',
                    'input' => 'datetime',
                    'format' => 'dd-MM-yyyy',
                    'invalid_message' => 'Value or character is not valid'
                ])
                ->add('closingDateExplanation', 'textarea')
                ->add('closingBalance', 'number', [ 'grouping' => true, 'precision' => 2])
                ->add('closingBalanceExplanation', 'textarea');
        }

        if ($this->options['showSubmitButton']) {
            $builder->add('save', 'submit');
        }
        if ($this->options['showDeleteButton']) {
            $builder->add('delete', 'submit');
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-accounts',
            'data_class' => 'AppBundle\Entity\Account',
            'validation_groups' => $this->options['showClosingBalance'] ? ['basic', 'closing_balance'] : ['basic'],
        ]);
    }

    public function getName()
    {
        return 'account';
    }

}