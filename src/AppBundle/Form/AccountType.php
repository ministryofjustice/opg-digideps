<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Form\Type\AccountNumberType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AppBundle\Entity\Account;

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
        'showSubmitButton' => true,
        'showDeleteButton' => false,
    ];


    /**
     * @param boolean showClosingBalance, default false
     * @param boolean showSubmitButton, default true
     * @param boolean showDeleteButton, default false
     */
    public function __construct(array $optionsOverride = [])
    {
        $this->options['id']='elvisform';
        $this->options = $optionsOverride + $this->options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bank', 'text');

        $this->addOpeningBalanceFields($builder);

        $builder->add('sortCode', new SortCodeType(), [ 'error_bubbling' => false])
            ->add('accountNumber', new AccountNumberType(), [ 'error_bubbling' => false]);


        if ($this->options['showClosingBalance']) {
            $this->addClosingBalanceFields($builder);
        }

        if ($this->options['showSubmitButton']) {
            $builder->add('save', 'submit');
        }
        if ($this->options['showDeleteButton']) {
            $builder->add('delete', 'submit');
        }
        $builder->add('js-enabled', 'hidden', ['data'=>'no', 'mapped'=>false]);
    }

    /**
     * Add fields: openingDate, openingBalance, openingDateExplanation
     * 
     * @param FormBuilderInterface $builder
     */
    protected function addOpeningBalanceFields(FormBuilderInterface $builder)
    {
        $builder
            ->add('openingDateMatchesReportDate', 'choice', [ 
                'choices' => ['yes'=>'Yes', 'no'=>'No'],
                'multiple' => false,
                'expanded' => true,
            ])
            // if JS is enabled and the openingDateMatchesReportDate is "yes", 
            // ovverride openingDate and openingDateExplanation values
            // (just before submitting the form)
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                
                if ('yes' == $data['js-enabled'] 
                    && isset($data['openingDateMatchesReportDate']) 
                    && $data['openingDateMatchesReportDate'] == Account::OPENING_DATE_SAME_YES
                ) {
                    $account = $event->getForm()->getData();
                    $reportStartdate = $account->getReportObject(true)->getStartDate();
                    
                    $data['openingDate'] = [
                        'day' => $reportStartdate->format('d'),
                        'month' => $reportStartdate->format('m'),
                        'year' => $reportStartdate->format('Y'),
                    ];
                    $data['openingDateExplanation'] = '';
                    
                    $event->setData($data);
                 }
           })
            ->add('openingDate', 'date', [ 'widget' => 'text',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd',
                'invalid_message' => 'account.openingDate.invalidMessage'
            ])
            ->add('openingBalance', 'number', [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'account.openingBalance.type'
            ])
            ->add('openingDateExplanation', 'textarea');
    }

    /**
     * Add fields: closingDate, closingDateExplanation, closingBalance, closingBalanceExplanation
     * 
     * @param FormBuilderInterface $builder
     */
    protected function addClosingBalanceFields(FormBuilderInterface $builder)
    {
        $builder->add('closingDate', 'date', [ 'widget' => 'text',
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'account.closingDate.invalidMessage'
            ])
            ->add('closingDateExplanation', 'textarea')
            ->add('closingBalance', 'number', [
                'grouping' => true,
                'precision' => 2,
                'invalid_message' => 'account.closingBalance.type'
            ])
            ->add('closingBalanceExplanation', 'textarea');
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