<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class MoneyOutExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('moneyOutExists', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new Constraints\NotBlank(['message' => 'moneyOut.moneyOutChoice.notBlank', 'groups' => ['money-out-exists']])],
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-out',
            'validation_groups' => ['money-out-exists'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'money_out_exists';
    }
}
