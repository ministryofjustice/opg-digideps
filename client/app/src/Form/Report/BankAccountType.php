<?php

namespace App\Form\Report;

use App\Entity\Report\BankAccount;
use App\Form\Type\SortCodeType;
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
    private int $step;

    private static function getBankAccountChoices(): array
    {
        $ret = [];
        foreach (BankAccount::$types as $key) {
            $ret['form.accountType.choices.' . $key] = $key;
        }

        return $ret;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->step = (int) $options['step'];

        $builder->add('id', FormTypes\HiddenType::class);

        if (1 === $this->step) {
            $builder->add('accountType', FormTypes\ChoiceType::class, [
                'choices' => self::getBankAccountChoices(),
                'expanded' => true,
                'placeholder' => 'Please select',
            ]);
        }

        if (2 === $this->step) {
            $builder->add('bank', FormTypes\TextType::class, [
                'required' => false,
            ]);
            $builder->add('accountNumber', FormTypes\TextType::class, ['attr' => ['maxlength' => 4]]);
            $builder->add('sortCode', SortCodeType::class, [
                'error_bubbling' => false,
                'constraints' => [
                    new NotBlank(['message' => 'account.sortCode.notBlank', 'groups' => ['bank-account-sortcode']]),
                    new Type(['type' => 'numeric', 'message' => 'account.sortCode.type', 'groups' => ['bank-account-sortcode']]),
                    new Length(['min' => 6, 'max' => 6, 'exactMessage' => 'account.sortCode.length', 'groups' => ['bank-account-sortcode']]),
                ],
            ]);
            $builder->add('isJointAccount', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ]);
        }

        if (3 === $this->step) {
            $builder->add('openingBalance', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'invalid_message' => 'account.openingBalance.type',
            ]);
            $builder->add('closingBalance', FormTypes\NumberType::class, [
                'scale' => 2,
                'grouping' => true,
                'invalid_message' => 'account.closingBalance.type',
                'required' => false,
            ]);
            $builder->add('isClosed', FormTypes\ChoiceType::class, [
                'choices' => array_flip([true => 'Yes', false => 'No']),
                'expanded' => true,
                'placeholder' => 'Please select',
                'invalid_message' => 'account.isClosed.notBlank',
            ]);
        }

        $builder->add('save', FormTypes\SubmitType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'account';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-bank-accounts',
            'validation_groups' => function (FormInterface $form) {
                /** @var BankAccount $formData */
                $formData = $form->getData();

                $step2Options = ['bank-account-number', 'bank-account-is-joint'];
                if ($formData->requiresSortCode()) {
                    $step2Options[] = 'bank-account-sortcode';
                }
                if ($formData->requiresBankName()) {
                    $step2Options[] = 'bank-account-name';
                }

                $step3Options = ['bank-account-opening-balance', 'bank-account-closing-balance'];
                if ($formData->isClosingBalanceZero()) {
                    $step3Options[] = 'bank-account-is-closed';
                }

                return [
                    1 => ['bank-account-type'],
                    2 => $step2Options,
                    3 => $step3Options,
                ][$this->step];
            },
        ])
        ->setRequired(['step']);
    }
}
