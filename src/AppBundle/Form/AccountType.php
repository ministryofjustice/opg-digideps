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
        $builder->add('id', 'hidden');
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
                'expanded' => true
            ])
            // Auto fill the openingDate field (with the same date as report start date) if either
            // - JS disabled and openingDate is not filled
            // - JS enabled and checkbox "openingDateMatchesReportDate" is set to "yes"
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                
                $data = $event->getData();
                $jsEnabled = ('yes' === $data['js-enabled']) ;
                    
                // no-JS in edit mode: always pre-select checkbox with "no" to enable validators
                if (!$jsEnabled && !empty($data['id'])) {
                    $data['openingDateMatchesReportDate'] = 'no';
                }
                
                $jsDisabledAndDateEmpty = !$jsEnabled
                    && empty($data['openingDate']['day'])
                    && empty($data['openingDate']['month'])
                    && empty($data['openingDate']['year']);
                
                $jsEnabledAndCheckboxYes = $jsEnabled
                    && isset($data['openingDateMatchesReportDate']) 
                    && $data['openingDateMatchesReportDate'] == Account::OPENING_DATE_SAME_YES;
                
                if ($jsDisabledAndDateEmpty || $jsEnabledAndCheckboxYes) {
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
        $showClosingBalance = $this->options['showClosingBalance'];
        
        $resolver->setDefaults([
            'translation_domain' => 'report-accounts',
            'data_class' => 'AppBundle\Entity\Account',
            'validation_groups' => function(FormInterface $form) use ($showClosingBalance) {
            	$validationGroups = ['basic'];
                
                $account = $form->getData(); /* @var $account Account */
                
                if ($showClosingBalance) {
                   $validationGroups[] = 'closing_balance'; 
                }
                
                if ($account->getOpeningDateMatchesReportDate() == Account::OPENING_DATE_SAME_NO) {
                    $validationGroups[] = 'opening_balance'; 
                }
                
            	return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'account';
    }

}