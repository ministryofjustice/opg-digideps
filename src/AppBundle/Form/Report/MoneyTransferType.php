<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
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
            $banks[$bank->getId()] = $bank->getBank() . ' ' . $bank->getAccountTypeText() . ' (****' . $bank->getAccountNumber() . ')';
        }


        if ($this->step == 1) {
            $builder
                ->add('accountFromId', 'choice', [
                        'choices' => $banks, 'empty_value' => 'Please select',]
                )->add('accountToId', 'choice', [
                        'choices' => $banks, 'empty_value' => 'Please select',]
                );
        }
        if ($this->step == 2) {
            $builder
                ->add('amount', 'number', [
                    'precision' => 2,
                    'grouping' => true,
                    'error_bubbling' => false,
                    'invalid_message' => 'transfer.amount.notNumeric',
                ]);
        }

        $builder
            ->add('save', 'submit');
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

    public function getName()
    {
        return 'money_transfers_type';
    }
}
