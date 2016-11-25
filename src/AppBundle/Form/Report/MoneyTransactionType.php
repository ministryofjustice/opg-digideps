<?php

namespace AppBundle\Form\Report;

use AppBundle\Validator\Constraints\Chain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Entity\Report\Account;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class MoneyTransactionType extends AbstractType
{
    private $step;

    /**
     * @param $step
     */
    public function __construct($step)
    {
        $this->step = (int)$step;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        if ($this->step === 1) {
            $builder->add('category', 'choice', [
                'choices' => [
                    //TODO from translations
                    1=>'Income from investment',
                    2=>'State Benefit',
                ],
                'expanded' => true,
                'empty_value' => 'Please select',
            ]);
        }

        if ($this->step === 2) {
            $builder->add('id', 'choice', [
                'choices' => [
                    //TODO from translations
                    'income-from-property-rental'=>'Income from property rental',
                    'dividends'=>'Dividend',
                ],
                'expanded' => true,
                'empty_value' => 'Please select',
            ]);
        }

        if ($this->step === 3) {
            $builder->add('moreDetails', 'textarea');
            $builder->add('amounts', 'collection', [
                'entry_type' => 'number',
                'allow_add' => true, //allow new fields added with JS
                'entry_options' => [
                    'error_bubbling' => false,
                    'precision' => 2,
                    'grouping' => true,
                    'invalid_message' => 'account.moneyInOut.amount.notNumeric',
                ],
            ]);
            $builder->add('createdAt', 'date', ['widget' => 'text',
                'mapped' => false, // Not in the model
                'input' => 'datetime',
                'format' => 'dd-MM-yyyy',
                'invalid_message' => 'Enter a valid date',
            ]);
        }

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'account';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-bank-accounts',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Report\Account */

                $validationGroups = [];

//                if ($this->step === 1) {
//                    $validationGroups = ['bank-account-type'];
//                }
//
//                if ($this->step === 2) {
//                    $validationGroups = ['bank-account-number'];
//                }
//
//                if ($this->step === 3) {
//                    $validationGroups = [
//                        'bank-account-opening-balance',
//                    ];
//                }

                return $validationGroups;
            },
        ]);
    }
}
