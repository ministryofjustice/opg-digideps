<?php

namespace AppBundle\Form\Odr;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IncomeBenefitsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('stateBenefits', 'collection', ['type' => new IncomeBenefitSingleType()])
            ->add('receiveStatePension', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('receiveOtherIncome', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('receiveOtherIncomeDetails', 'textarea')
            ->add('expectCompensationDamages', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('expectCompensationDamagesDetails', 'textarea')
            ->add('oneOff', 'collection', ['type' => new IncomeBenefitSingleType()])
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Odr\Odr',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                /* @var $data Odr */
                $validationGroups = ['odr-state-benefits'];

                if ($data->getReceiveOtherIncome() == 'yes') {
                    $validationGroups[] = 'receive-other-income-yes';
                }

                if ($data->getExpectCompensationDamages() == 'yes') {
                    $validationGroups[] = 'expect-compensation-damage-yes';
                }

                return $validationGroups;
            },
            // enable validation on AccountTransactionSingleType collections
            'cascade_validation' => true,
            'translation_domain' => 'odr-income-benefits',
        ]);
    }

    public function getName()
    {
        return 'odr_income_benefits';
    }
}
