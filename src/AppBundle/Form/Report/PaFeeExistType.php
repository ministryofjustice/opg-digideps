<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaFeeExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasFees', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'fee.noFeesChoice.notBlank', 'groups' => ['fee-exist']])],
            ])
            ->add('reasonForNoFees', 'textarea')
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-pa-fee-expense',
            'validation_groups' => function (FormInterface $form) {
                $validationGroups = ['fee-exist'];
                if ($form['hasFees']->getData() === 'no') {
                    $validationGroups = ['reasonForNoFees'];
                }

                return $validationGroups;
            },
        ]);
    }

    public function getName()
    {
        return 'fee_exist';
    }
}
