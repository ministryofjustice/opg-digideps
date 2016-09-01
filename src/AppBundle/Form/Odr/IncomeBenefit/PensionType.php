<?php

namespace AppBundle\Form\Odr\IncomeBenefit;

use AppBundle\Entity\Odr\Odr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PensionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden')
            ->add('receiveStatePension', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('receiveOtherIncome', 'choice', array(
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
            ))
            ->add('receiveOtherIncomeDetails', 'textarea')
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Odr\Odr',
            'validation_groups' => function (FormInterface $form) {

                $data = $form->getData();
                /* @var $data Odr */
                $validationGroups = [
                    'receive-state-pension',
                    'receive-other-income',
                ];

                if ($data->getReceiveOtherIncome() == 'yes') {
                    $validationGroups[] = 'receive-other-income-yes';
                }

                return $validationGroups;
            },
            'cascade_validation' => true,
            'translation_domain' => 'odr-income-benefits',
        ]);
    }

    public function getName()
    {
        return 'odr_income_pension';
    }
}
