<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class DoesMoneyOutExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('doesMoneyOutExist', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new Constraints\NotBlank(['message' => 'moneyOut.moneyOutChoice.notBlank', 'groups' => ['does-money-out-exist']])],
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-out',
            'validation_groups' => ['does-money-out-exist'],
        ])
        ->setAllowedTypes('translation_domain', 'string');
    }

    public function getBlockPrefix()
    {
        return 'does_money_out_exist';
    }
}
