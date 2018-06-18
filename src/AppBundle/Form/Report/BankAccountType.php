<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\BankAccount;
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
                'choices'     => array_map(function($key){
                    return 'form.accountType.choices.' . $key;
                }, BankAccount::$types),
                'expanded'    => true,
                'empty_value' => 'Please select',
            ]);
        }

        if ($this->step === 2) {
            $builder->add('bank', FormTypes\TextType::class, [
                'required' => false,
            ]);
            $builder->add('accountNumber', FormTypes\TextType::class, ['max_length' => 4]);
            $builder->add('sortCode', new SortCodeType(), [
                'error_bubbling' => false,
                'required'       => false,
                'constraints'    => new Chain([
                    'constraints' => [
                        new NotBlank(['message' => 'account.sortCode.notBlank', 'groups' => ['bank-account-sortcode']]),
                        new Type(['type' => 'numeric', 'message' => 'account.sortCode.type', 'groups' => ['bank-account-sortcode']]),
                        new Length(['min' => 6, 'max' => 6, 'exactMessage' => 'account.sortCode.length', 'groups' => ['bank-account-sortcode']]),
                    ],
                    'stopOnError' => true,
                    'groups'      => ['bank-account-sortcode'],
                ]),
            ]);
            $builder->add('isJointAccount', FormTypes\ChoiceType::class, [
                'choices'  => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('openingBalance', FormTypes\NumberType::class, [
                'precision'       => 2,
                'grouping'        => true,
                'invalid_message' => 'account.openingBalance.type',
            ]);
            $builder->add('closingBalance', FormTypes\NumberType::class, [
                'precision'       => 2,
                'grouping'        => true,
                'invalid_message' => 'account.closingBalance.type',
                'required'        => false,
            ]);
        }

        if ($this->step === 4) {
            $builder->add('isClosed', FormTypes\ChoiceType::class, [
                'choices'     => [true => 'Yes', false => 'No'],
                'expanded'    => true,
                'empty_value' => 'Please select',
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
            'translation_domain' => 'report-bank-accounts',
            'validation_groups'  => function (FormInterface $form) {
                $step2Options = ['bank-account-number', 'bank-account-is-joint'];
                if ($form->getData()->requiresSortCode()) {
                    $step2Options[] = 'bank-account-sortcode';
                }
                if ($form->getData()->requiresBankName()) {
                    $step2Options[] = 'bank-account-name';
                }

                return [
                    1 => ['bank-account-type'],
                    2 => $step2Options,
                    3 => ['bank-account-opening-balance'],
                    4 => 'bank-account-is-closed'
                ][$this->step];
            },
        ])
        ->setRequired(['step']);
    }
}
