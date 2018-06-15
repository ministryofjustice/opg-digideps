<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyTransferType extends AbstractType
{
    private $step;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->step = (int) $options['step'];

        $banks = [];
        foreach ($options['banks'] as $bank) {
            /* $var $bank \AppBundle\Entity\Report\BankAccount */
            $banks[$bank->getId()] = $bank->getNameOneLine();
        }


        if ($this->step == 1) {
            $builder
                ->add('accountFromId', FormTypes\ChoiceType::class, [
                        'choices' => $banks, 'placeholder' => 'Please select',]
                )->add('accountToId', FormTypes\ChoiceType::class, [
                        'choices' => $banks, 'placeholder' => 'Please select',]
                );
        }
        if ($this->step == 2) {
            $builder
                ->add('amount', FormTypes\NumberType::class, [
                    'precision' => 2,
                    'grouping' => true,
                    'error_bubbling' => false,
                    'invalid_message' => 'transfer.amount.notNumeric',
                ]);
        }

        $builder
            ->add('save', FormTypes\SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-transfer',
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Report\MoneyTransfer */

                $validationGroups = [];

                if ($this->step === 1) {
                    $validationGroups[] = 'money-transfer-account-from';
                    $validationGroups[] = 'money-transfer-account-to';
                }
                if ($this->step === 2) {
                    $validationGroups[] = 'money-transfer-amount';
                }

                return $validationGroups;
            },
        ])
        ->setRequired(['step', 'banks'])
        ->setAllowedTypes('banks', 'array')
        ;
    }

    public function getBlockPrefix()
    {
        return 'money_transfers_type';
    }
}
