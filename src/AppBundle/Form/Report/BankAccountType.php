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

class BankAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('accountType', 'choice', [
            'choices' => Account::$types,
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
            'constraints' => new Chain([
                'constraints' => [
                    new NotBlank(['groups' => ['sortcode'], 'message'=>'account.sortCode.notBlank']),
                    new Type(['type' => 'numeric', 'message'=>'account.sortCode.type', 'groups' => ['sortcode']]),
                    new Length(['min'=>6, 'max'=>6, 'exactMessage' => 'account.sortCode.length', 'groups' => ['sortcode']]),
                ],
                'stopOnError' => true,
                'groups' => ['sortcode'],
            ]),
        ]);

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
        $builder->add('isClosedDisplayed', 'hidden', [
            'required' => false,
            'mapped' => false,
        ]);
        $builder->add('isJointAccount', 'choice', array(
            'choices' => ['yes' => 'Yes', 'no' => 'No'],
            'expanded' => true,
        ));

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

                $data = $form->getData();
                /* @var $data \AppBundle\Entity\Report\Account */
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
