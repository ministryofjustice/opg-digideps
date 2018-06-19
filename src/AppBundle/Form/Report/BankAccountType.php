<?php

namespace AppBundle\Form\Report;

use AppBundle\Entity\Report\BankAccount;
use AppBundle\Form\Type\SortCodeType;
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

    /**
     * @return array
     */
    private static function getBankAccountChoices()
    {
        $ret = [];
        foreach(BankAccount::$types as $key){
            $ret[$key] = 'form.accountType.choices.' . $key;
        }
        return $ret;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        $builder->add('id', FormTypes\HiddenType::class);

        if ($this->step === 1) {
            $builder->add('accountType', FormTypes\ChoiceType::class, [
                'choices'     => self::getBankAccountChoices(),
                'expanded'    => true,
                'placeholder' => 'Please select',
            ]);
        }

        if ($this->step === 2) {
            $builder->add('bank', FormTypes\TextType::class, [
                'required' => false,
            ]);
            $builder->add('accountNumber', FormTypes\TextType::class, ['attr'=>['maxlength' => 4]]);
            $builder->add('sortCode', SortCodeType::class, [
                'error_bubbling' => false,
                'required'       => false,
                'constraints'    => [
                    new NotBlank(['message' => 'account.sortCode.notBlank', 'groups' => ['bank-account-sortcode']]),
                    new Type(['type' => 'numeric', 'message' => 'account.sortCode.type', 'groups' => ['bank-account-sortcode']]),
                    new Length(['min' => 6, 'max' => 6, 'exactMessage' => 'account.sortCode.length', 'groups' => ['bank-account-sortcode']]),
                ],
            ]);
            $builder->add('isJointAccount', FormTypes\ChoiceType::class, [
                'choices'  => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ]);
        }

        if ($this->step === 3) {
            $builder->add('openingBalance', FormTypes\NumberType::class, [
                'scale'       => 2,
                'grouping'        => true,
                'invalid_message' => 'account.openingBalance.type',
            ]);
            $builder->add('closingBalance', FormTypes\NumberType::class, [
                'scale'       => 2,
                'grouping'        => true,
                'invalid_message' => 'account.closingBalance.type',
                'required'        => false,
            ]);
        }

        if ($this->step === 4) {
            $builder->add('isClosed', FormTypes\ChoiceType::class, [
                'choices'     => array_flip([true => 'Yes', false => 'No']),
                'expanded'    => true,
                'placeholder' => 'Please select',
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
