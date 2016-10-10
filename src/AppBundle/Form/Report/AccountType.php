<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Entity\Report\Account;

class AccountType extends AbstractType
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
            $builder->add('accountType', 'choice', [
                'choices' => Account::$types,
                'expanded' => true,
                'empty_value' => 'Please select',
            ]);
        }

        if ($this->step === 2) {
            $builder->add('bank', 'text', [
                'required' => false,
            ]);
            $builder->add('accountNumber', 'text', ['max_length' => 4]);
            $builder->add('sortCode', new SortCodeType(), [
                'error_bubbling' => false,
                'required' => false,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('openingBalance', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'account.openingBalance.type',
            ]);
            $builder->add('closingBalance', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'account.closingBalance.type',
                'required' => false,
            ]);
            $builder->add('isClosed', 'checkbox', [
                'required' => false,
            ]);
            $builder->add('isJointAccount', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ));
        }

//        $builder->add('isClosedDisplayed', 'hidden', [
//            'required' => false,
//            'mapped' => false,
//        ]);

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'account';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-account-form',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData(); /* @var $data \AppBundle\Entity\Report\Account */

                $validationGroups = [];

                if ($this->step === 1) {
                    $validationGroups = ['bank-account-type'];
                }

                if ($this->step === 2) {
                    $validationGroups = ['bank-account-name', 'bank-account-number', 'bank-account-sortcode'];
                }

                if ($this->step === 3) {

                }

                //                $validationGroups = ['add_edit'];
//
//                if ($data->requiresBankNameAndSortCode()) {
//                    $validationGroups[] = 'sortcode';
//                    $validationGroups[] = 'bank_name';
//                }

                return $validationGroups;
            },
        ]);
    }
}
