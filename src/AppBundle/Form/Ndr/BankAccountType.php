<?php

namespace AppBundle\Form\Ndr;

use AppBundle\Entity\Account;
use AppBundle\Entity\Ndr\BankAccount;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Validator\Constraints\Chain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class BankAccountType extends AbstractType
{
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        $builder->add('id', FormTypes\HiddenType::class);

        if ($this->step === 1) {
            $builder->add('accountType', FormTypes\ChoiceType::class, [
                'choices' => array_flip(BankAccount::$types),
                'expanded' => true,
                'placeholder' => 'Please select',
            ]);
        }

        if ($this->step === 2) {
            $builder->add('bank', FormTypes\TextType::class, [
                'required' => false,
            ]);
            $builder->add('accountNumber', FormTypes\TextType::class, ['attr'=> ['maxlength' => 4]]);
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
            $builder->add('isJointAccount', FormTypes\ChoiceType::class, [
                'choices'  => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('balanceOnCourtOrderDate', FormTypes\NumberType::class, [
                'precision' => 2,
                'grouping' => true,
                'invalid_message' => 'ndr.account.balanceOnCourtOrderDate.type',

            ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function getBlockPrefix()
    {
        return 'account';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'ndr-bank-accounts',
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
        ])
        ->setRequired(['step']);
    }
}
