<?php

namespace App\Form\Report;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormTypes;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class DoesMoneyInExistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('doesMoneyInExist', FormTypes\ChoiceType::class, [
                'choices' => ['Yes' => 'Yes', 'No' => 'No'],
                'expanded' => true,
                'mapped' => false,
                'constraints' => [new Constraints\NotBlank(['message' => 'moneyIn.moneyInChoice.notBlank', 'groups' => ['does-money-in-exist']])],
            ])
            ->add('save', FormTypes\SubmitType::class, ['label' => 'save.label']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'report-money-in',
            'validation_groups' => ['does-money-in-exist'],
        ])
        ->setAllowedTypes('translation_domain', 'string');
    }

    public function getBlockPrefix()
    {
        return 'does_money_in_exist';
    }
}
