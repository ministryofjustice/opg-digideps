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
        'jsEnabled' => false,
    ];


    /**
     * @param boolean showClosingBalance, default false
     * @param boolean showSubmitButton, default true
     * @param boolean showDeleteButton, default false
     */
    public function __construct(array $optionsOverride = [])
    {
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
            // PRE_SET_DATA
            // Auto set the checkbox value (to "yes" if the date matches, to "no" otherwise) if in edit mode 
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $account = $event->getData(); /* @var $account Account */
                
                // skip if not in edit mode
                if (!$account->getId()) {
                    return;
                }
                
                $dateMatching = $account->getOpeningDate()->format('dmY') === $account->getReportObject(true)->getStartDate()->format('dmY');
                $account->setOpeningDateMatchesReportDate(
                    $dateMatching ? Account::OPENING_DATE_SAME_YES : Account::OPENING_DATE_SAME_NO
                );
                
            })
            // PRE_SUBMIT:
            // Auto fill the openingDate field (with the same date as report start date) if either
            // - JS disabled and openingDate is not filled
            // - JS enabled and checkbox "openingDateMatchesReportDate" is set to "yes"
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                
                $data = $event->getData();
                $account = $event->getForm()->getData();  /* @var $account Account */
                
                $jsEnabled = ('yes' === $data['js-enabled']) ;
                $jsDisabled = ('no' === $data['js-enabled']) ;
                // $editMode = !empty($data['id']);
                $openingDateNotFilled = empty($data['openingDate']['day'])
                    && empty($data['openingDate']['month'])
                    && empty($data['openingDate']['year']);
                $checkboxYes = isset($data['openingDateMatchesReportDate']) 
                    && $data['openingDateMatchesReportDate'] == Account::OPENING_DATE_SAME_YES; 
                    
                // no-JS: always pre-select checkbox with "no" as the opening date is always shown
                if ($jsDisabled ) {
                    $data['openingDateMatchesReportDate'] = 'no';
                }
                
                if (
                    ($jsDisabled && $openingDateNotFilled) 
                    || 
                    ($jsEnabled && $checkboxYes)
                ) {
                    
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
        $jsEnabled = $this->options['jsEnabled'];
        
        $resolver->setDefaults([
            'translation_domain' => 'report-accounts',
            'data_class' => 'AppBundle\Entity\Account',
            'validation_groups' => function(FormInterface $form) use ($showClosingBalance, $jsEnabled) {
            	$validationGroups = ['basic', 'opening_balance'];
                
                //$account = $form->getData(); /* @var $account Account */
                
                if ($showClosingBalance) {
                   $validationGroups[] = 'closing_balance'; 
                }

                // skip validation for checkbox in non-JS mode
                if ($jsEnabled) {
                    $validationGroups[] = 'checkbox_matches_date'; 
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