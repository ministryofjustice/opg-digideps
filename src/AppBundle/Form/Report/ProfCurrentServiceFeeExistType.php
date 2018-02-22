<?php

namespace AppBundle\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfCurrentServiceFeeExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hasFees', 'choice', [
                'choices' => ['yes' => 'Yes', 'no' => 'No'],
                'expanded' => true,
                'constraints' => [new NotBlank(['message' => 'fee.noFeesChoice.notBlank', 'groups' => ['fee-exist']])],
            ])
            ->add('save', 'submit', ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
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
