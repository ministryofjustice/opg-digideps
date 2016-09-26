<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\BankAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use AppBundle\Form\Type\SortCodeType;
use AppBundle\Entity\Account;

class BankAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('accountType', 'choice', [
            'choices' => BankAccount::$types,
            'expanded' => false,
            'empty_value' => 'Please select',
        ]);
        $builder->add('bank', 'text', [
            'required' => false,
        ]);
        $builder->add('accountNumber', 'text', ['max_length' => 4]);
        $builder->add('sortCode', new SortCodeType(), [
            'error_bubbling' => false,
            'required' => false,
        ]);

        $builder->add('balanceOnCourtOrderDate', 'number', [
            'precision' => 2,
            'grouping' => true,
            'invalid_message' => 'odr.account.balanceOnCourtOrderDate.type',

        ]);

        $builder->add('save', 'submit');
    }

    public function getName()
    {
        return 'account';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'odr-account-form',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData(); /* @var $data \AppBundle\Entity\Odr\BankAccount */
                $validationGroups = ['add_edit'];

                if ($data->requiresBankNameAndSortCode()) {
                    $validationGroups[] = 'sortcode';
                    $validationGroups[] = 'bank_name';
                }

                return $validationGroups;
            },
        ]);
    }
}
