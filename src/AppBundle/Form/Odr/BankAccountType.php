<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Account;
use AppBundle\Entity\Odr\BankAccount;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Validator\Constraints\Chain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class BankAccountType extends AbstractType
{
    private $step;

    /**
     * @param $step
     */
    public function __construct($step)
    {
        $this->step = (int) $step;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        if ($this->step === 1) {
            $builder->add('accountType', 'choice', [
                'choices' => BankAccount::$types,
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
                'constraints' => new Chain([
                    'constraints' => [
                        new NotBlank(['groups' => ['sortcode'], 'message' => 'account.sortCode.notBlank']),
                        new Type(['type' => 'numeric', 'message' => 'account.sortCode.type', 'groups' => ['sortcode']]),
                        new Length(['min' => 6, 'max' => 6, 'exactMessage' => 'account.sortCode.length', 'groups' => ['sortcode']]),
                    ],
                    'stopOnError' => true,
                    'groups' => ['sortcode'],
                ]),
            ]);
            $builder->add('isJointAccount', 'choice', [
                'choices'  => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('balanceOnCourtOrderDate', 'number', [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'odr.account.balanceOnCourtOrderDate.type',

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
            'translation_domain' => 'odr-bank-accounts',
            'validation_groups'  => function (FormInterface $form) {

                $step2Options = ['bank-account-number', 'bank-account-is-joint'];
                if ($form->getData()->requiresSortCode()) {
                    $step2Options[] = 'sortcode';
                }
                if ($form->getData()->requiresBankName()) {
                    $step2Options[] = 'bank-account-name';
                }

                return [
                    1 => ['bank-account-type'],
                    2 => $step2Options,
                    3 => ['bank-account-balance-on-cot'],
                ][$this->step];
            },
        ]);
    }
}
